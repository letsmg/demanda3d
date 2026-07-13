package main

import (
	"context"
	"encoding/json"
	"fmt"
	"log"
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
	Channel   string `json:"channel"`   // push, email, sms
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
	cfg := loadConfig()

	log.Println("========================================")
	log.Println("  Go Notification Service - Demanda3D")
	log.Println("========================================")
	log.Printf("Redis: %s:%s", cfg.RedisHost, cfg.RedisPort)
	log.Printf("Queue: %s", cfg.QueueName)
	log.Printf("Workers: %d goroutines", cfg.Concurrency)
	log.Println("========================================")

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
		log.Fatalf("❌ Falha ao conectar ao Redis: %v", err)
	}
	log.Println("✅ Conectado ao Redis com sucesso")

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
					log.Printf("⚠️ Erro ao ler da fila: %v", err)
					time.Sleep(1 * time.Second)
					continue
				}

				// result[0] = key, result[1] = value
				if len(result) < 2 {
					continue
				}

				var notif Notification
				if err := json.Unmarshal([]byte(result[1]), &notif); err != nil {
					log.Printf("⚠️ Payload inválido: %v (raw: %s)", err, result[1])
					continue
				}

				tasks <- &notif
			}
		}
	}()

	// Aguarda sinal de shutdown
	<-sigCh
	log.Println("🛑 Sinal de shutdown recebido. Finalizando workers...")
	cancel()
	close(tasks)
	wg.Wait()
	log.Println("✅ Go Notification Service finalizado com sucesso")
}

// worker processa notificações recebidas do canal.
func worker(id int, tasks <-chan *Notification, wg *sync.WaitGroup) {
	defer wg.Done()
	log.Printf("🚀 Worker #%d iniciado", id)

	for notif := range tasks {
		processNotification(id, notif)
	}

	log.Printf("🛑 Worker #%d finalizado", id)
}

// processNotification simula o disparo de notificação.
// Em produção, aqui entrariam chamadas reais para FCM (Firebase),
// APNs (Apple), SendGrid, Twilio, etc.
func processNotification(workerID int, n *Notification) {
	start := time.Now()

	log.Printf("📨 [Worker #%d] Processando: user=%s channel=%s title=%q",
		workerID, n.UserID, n.Channel, n.Title)

	// Simula latência de envio (50-200ms)
	time.Sleep(50 * time.Millisecond)

	// Mock de disparo por canal
	switch n.Channel {
	case "push":
		log.Printf("  📲 [FCM Mock] Push enviado para user=%s: %s", n.UserID, n.Title)
	case "email":
		log.Printf("  📧 [SMTP Mock] E-mail enviado para user=%s: %s", n.UserID, n.Title)
	case "sms":
		log.Printf("  💬 [SMS Mock] SMS enviado para user=%s: %s", n.UserID, n.Title)
	default:
		log.Printf("  📢 [Default] Notificação genérica para user=%s: %s", n.UserID, n.Title)
	}

	elapsed := time.Since(start)
	log.Printf("  ✅ [Worker #%d] Concluído em %v", workerID, elapsed)
}