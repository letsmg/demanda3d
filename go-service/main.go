package main

import (
	"context"
	"encoding/json"
	"fmt"
	"log" // Mantido apenas para log.Fatalf no erro fatal de inicialização
	"log/slog"
	"os"
	"os/signal"
	"sync"
	"syscall"
	"time"

	"github.com/redis/go-redis/v9"
)

// Notification representa o payload recebido do Laravel.
type Notification struct {
	UserID    string `json:"user_id"`
	Title     string `json:"title"`
	Message   string `json:"message"`
	Channel   string `json:"channel"` // push, email, sms
	TenantID  string `json:"tenant_id"`
	Timestamp string `json:"timestamp"`
}

// Config carrega variáveis de ambiente.
type Config struct {
	RedisHost     string
	RedisPort     string
	RedisPassword string
	RedisDB       int
	QueueName     string
	Concurrency   int
}

func loadConfig() Config {
	return Config{
		RedisHost:     getEnv("REDIS_HOST", "demanda-redis-dev"),
		RedisPort:     getEnv("REDIS_PORT", "6379"),
		RedisPassword: getEnv("REDIS_PASSWORD", ""),
		RedisDB:       0,
		QueueName:     getEnv("NOTIFICATIONS_QUEUE", "notifications_queue"),
		Concurrency:   5, // número de goroutines workers
	}
}

func getEnv(key, fallback string) string {
	if v := os.Getenv(key); v != "" {
		return v
	}
	return fallback
}

func main() {
	// 1. Configura o Slog para gerar JSON estruturado na saída padrão
	logger := slog.New(slog.NewJSONHandler(os.Stdout, &slog.HandlerOptions{
		Level: slog.LevelInfo,
	}))
	slog.SetDefault(logger)

	cfg := loadConfig()

	slog.Info("Iniciando Go Notification Service",
		slog.String("service", "demanda3d-notifications"),
		slog.String("redis_host", cfg.RedisHost),
		slog.String("redis_port", cfg.RedisPort),
		slog.String("queue", cfg.QueueName),
		slog.Int("workers", cfg.Concurrency),
	)

	// Conexão com Redis
	rdb := redis.NewClient(&redis.Options{
		Addr:     fmt.Sprintf("%s:%s", cfg.RedisHost, cfg.RedisPort),
		Password: cfg.RedisPassword,
		DB:       cfg.RedisDB,
	})

	ctx, cancel := context.WithCancel(context.Background())
	defer cancel()

	// Healthcheck inicial
	if err := rdb.Ping(ctx).Err(); err != nil {
		log.Fatalf("❌ Falha fatal ao conectar ao Redis: %v", err)
	}
	slog.Info("Conectado ao Redis com sucesso!")

	// Graceful shutdown
	sigCh := make(chan os.Signal, 1)
	signal.Notify(sigCh, syscall.SIGINT, syscall.SIGTERM)

	// Worker pool
	var wg sync.WaitGroup
	tasks := make(chan *Notification, cfg.Concurrency*2)

	// Inicia workers
	for i := 0; i < cfg.Concurrency; i++ {
		wg.Add(1)
		go worker(i, tasks, &wg)
	}

	// Main loop: BLPOP bloqueante na fila
	go func() {
		for {
			select {
			case <-ctx.Done():
				return
			default:
				result, err := rdb.BLPop(ctx, 5*time.Second, cfg.QueueName).Result()
				if err != nil {
					// Timeout é esperado — apenas continua
					if err == redis.Nil {
						continue
					}
					slog.Error("Erro ao ler da fila", slog.Any("error", err))
					time.Sleep(1 * time.Second)
					continue
				}

				// result[0] = key, result[1] = value
				if len(result) < 2 {
					continue
				}

				var notif Notification
				if err := json.Unmarshal([]byte(result[1]), &notif); err != nil {
					slog.Warn("Payload inválido recebido do Laravel",
						slog.Any("error", err),
						slog.String("raw_payload", result[1]),
					)
					continue
				}

				tasks <- &notif
			}
		}
	}()

	// Aguarda sinal de shutdown
	<-sigCh
	slog.Info("Sinal de shutdown recebido. Finalizando workers...")
	cancel()
	close(tasks)
	wg.Wait()
	slog.Info("Go Notification Service finalizado com sucesso!")
}

// worker processa notificações recebidas do canal.
func worker(id int, tasks <-chan *Notification, wg *sync.WaitGroup) {
	defer wg.Done()
	slog.Info("Worker iniciado", slog.Int("worker_id", id))

	for notif := range tasks {
		processNotification(id, notif)
	}

	slog.Info("Worker finalizado", slog.Int("worker_id", id))
}

// processNotification processa e simula o envio estruturado para o Loki
func processNotification(workerID int, n *Notification) {
	start := time.Now()

	slog.Info("Processando envio de notificação",
		slog.Int("worker_id", workerID),
		slog.String("user_id", n.UserID),
		slog.String("tenant_id", n.TenantID),
		slog.String("channel", n.Channel),
		slog.String("title", n.Title),
	)

	// Simula latência de envio (50-200ms)
	time.Sleep(50 * time.Millisecond)

	// Simulação real de canal
	switch n.Channel {
	case "push":
		slog.Info("Push disparado (Mock FCM)", slog.String("user_id", n.UserID), slog.String("title", n.Title))
	case "email":
		slog.Info("E-mail disparado (Mock SMTP)", slog.String("user_id", n.UserID), slog.String("title", n.Title))
	case "sms":
		slog.Info("SMS disparado (Mock Provider)", slog.String("user_id", n.UserID), slog.String("title", n.Title))
	default:
		slog.Warn("Tentativa de envio em canal desconhecido", slog.String("channel", n.Channel))
	}

	elapsed := time.Since(start)
	
	// Registra a conclusão com métrica de duração para facilitar a criação de gráficos de performance no Grafana
	slog.Info("Notificação concluída",
		slog.Int("worker_id", workerID),
		slog.String("user_id", n.UserID),
		slog.Duration("duration", elapsed),
	)
}