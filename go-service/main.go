package main

import (
	"context"
	"encoding/json"
	"fmt"
	"log"
	"log/slog"
	"os"
	"os/signal"
	"sync"
	"syscall"
	"time"

	amqp "github.com/rabbitmq/amqp091-go"
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

// ChatMessage representa o payload de mensagens de chat (dúvidas e disputas).
type ChatMessage struct {
	ThreadID   string `json:"thread_id"`
	SenderType string `json:"sender_type"` // staff, client, admin
	SenderID   string `json:"sender_id"`
	Content    string `json:"content"` // já sanitizado pelo MessageSanitizer do Laravel
	TenantID   string `json:"tenant_id"`
	Channel    string `json:"channel"` // "chat" para dúvidas, "dispute" para disputas
}

// Config carrega variáveis de ambiente.
type Config struct {
	RabbitMQHost     string
	RabbitMQPort     string
	RabbitMQUser     string
	RabbitMQPassword string
	RabbitMQVHost    string
	NotificationQueue string
	ChatQueue        string
	DisputeQueue     string
	Concurrency      int
}

func loadConfig() Config {
	return Config{
		RabbitMQHost:      getEnv("RABBITMQ_HOST", "demanda-rabbitmq-dev"),
		RabbitMQPort:      getEnv("RABBITMQ_PORT", "5672"),
		RabbitMQUser:      getEnv("RABBITMQ_USER", "guest"),
		RabbitMQPassword:  getEnv("RABBITMQ_PASSWORD", "guest"),
		RabbitMQVHost:     getEnv("RABBITMQ_VHOST", "/"),
		NotificationQueue: getEnv("NOTIFICATIONS_QUEUE", "notifications_queue"),
		ChatQueue:         getEnv("CHAT_QUEUE", "chat_queue"),
		DisputeQueue:      getEnv("DISPUTE_QUEUE", "dispute_queue"),
		Concurrency:       5,
	}
}

func getEnv(key, fallback string) string {
	if v := os.Getenv(key); v != "" {
		return v
	}
	return fallback
}

// Consumer interface para handlers de fila.
type Consumer interface {
	QueueName() string
	Handle(payload []byte) error
	WorkerName() string
}

// NotificationConsumer processa notificações.
type NotificationConsumer struct {
	cfg Config
}

func (c *NotificationConsumer) QueueName() string  { return c.cfg.NotificationQueue }
func (c *NotificationConsumer) WorkerName() string  { return "notification" }

func (c *NotificationConsumer) Handle(payload []byte) error {
	var notif Notification
	if err := json.Unmarshal(payload, &notif); err != nil {
		slog.Warn("Payload de notificação inválido",
			slog.Any("error", err),
			slog.String("raw_payload", string(payload)),
		)
		return fmt.Errorf("json unmarshal: %w", err)
	}
	processNotification(&notif)
	return nil
}

// ChatConsumer processa mensagens de chat (dúvidas e disputas).
type ChatConsumer struct {
	cfg     Config
	channel string // "chat" ou "dispute"
}

func (c *ChatConsumer) QueueName() string {
	if c.channel == "dispute" {
		return c.cfg.DisputeQueue
	}
	return c.cfg.ChatQueue
}

func (c *ChatConsumer) WorkerName() string {
	return c.channel
}

func (c *ChatConsumer) Handle(payload []byte) error {
	var msg ChatMessage
	if err := json.Unmarshal(payload, &msg); err != nil {
		slog.Warn("Payload de chat inválido",
			slog.Any("error", err),
			slog.String("raw_payload", string(payload)),
		)
		return fmt.Errorf("json unmarshal: %w", err)
	}
	processChatMessage(&msg)
	return nil
}

func main() {
	// Configura Slog JSON estruturado
	logger := slog.New(slog.NewJSONHandler(os.Stdout, &slog.HandlerOptions{
		Level: slog.LevelInfo,
	}))
	slog.SetDefault(logger)

	cfg := loadConfig()

	slog.Info("Iniciando Go Notification & Chat Service",
		slog.String("service", "demanda3d-messaging"),
		slog.String("rabbitmq_host", cfg.RabbitMQHost),
		slog.String("rabbitmq_port", cfg.RabbitMQPort),
		slog.String("notifications_queue", cfg.NotificationQueue),
		slog.String("chat_queue", cfg.ChatQueue),
		slog.String("dispute_queue", cfg.DisputeQueue),
		slog.Int("workers", cfg.Concurrency),
	)

	// Conexão com RabbitMQ
	amqpURL := fmt.Sprintf("amqp://%s:%s@%s:%s/%s",
		cfg.RabbitMQUser, cfg.RabbitMQPassword,
		cfg.RabbitMQHost, cfg.RabbitMQPort, cfg.RabbitMQVHost,
	)

	conn, err := amqp.Dial(amqpURL)
	if err != nil {
		log.Fatalf("❌ Falha fatal ao conectar ao RabbitMQ: %v", err)
	}
	defer conn.Close()

	ch, err := conn.Channel()
	if err != nil {
		log.Fatalf("❌ Falha fatal ao abrir canal RabbitMQ: %v", err)
	}
	defer ch.Close()

	slog.Info("Conectado ao RabbitMQ com sucesso!")

	ctx, cancel := context.WithCancel(context.Background())
	defer cancel()

	// Graceful shutdown
	sigCh := make(chan os.Signal, 1)
	signal.Notify(sigCh, syscall.SIGINT, syscall.SIGTERM)

	// Consumidores registrados
	consumers := []Consumer{
		&NotificationConsumer{cfg: cfg},
		&ChatConsumer{cfg: cfg, channel: "chat"},
		&ChatConsumer{cfg: cfg, channel: "dispute"},
	}

	// Worker pool compartilhado
	tasks := make(chan func(), cfg.Concurrency*2)
	var wg sync.WaitGroup

	for i := 0; i < cfg.Concurrency; i++ {
		wg.Add(1)
		go func(workerID int) {
			defer wg.Done()
			slog.Info("Worker iniciado", slog.Int("worker_id", workerID))
			for task := range tasks {
				task()
			}
			slog.Info("Worker finalizado", slog.Int("worker_id", workerID))
		}(i)
	}

	// Inicia consumidores para cada fila
	for _, consumer := range consumers {
		go func(c Consumer) {
			q, err := ch.QueueDeclare(
				c.QueueName(),
				true,  // durable
				false, // auto-delete
				false, // exclusive
				false, // no-wait
				nil,
			)
			if err != nil {
				slog.Error("Falha ao declarar fila",
					slog.String("queue", c.QueueName()),
					slog.Any("error", err),
				)
				return
			}

			msgs, err := ch.Consume(
				q.Name,
				"",
				false, // auto-ack desabilitado — confirmação manual
				false,
				false,
				false,
				nil,
			)
			if err != nil {
				slog.Error("Falha ao consumir fila",
					slog.String("queue", c.QueueName()),
					slog.Any("error", err),
				)
				return
			}

			slog.Info("Escutando fila",
				slog.String("queue", c.QueueName()),
				slog.String("worker", c.WorkerName()),
			)

			for {
				select {
				case <-ctx.Done():
					return
				case msg, ok := <-msgs:
					if !ok {
						return
					}
					// Enfileira no worker pool
					consumerCopy := c
					msgCopy := msg
					tasks <- func() {
						start := time.Now()
						if err := consumerCopy.Handle(msgCopy.Body); err != nil {
							slog.Error("Erro ao processar mensagem",
								slog.String("queue", consumerCopy.QueueName()),
								slog.String("worker", consumerCopy.WorkerName()),
								slog.Any("error", err),
							)
							// Nack com re-queue em caso de erro
							_ = msgCopy.Nack(false, true)
							return
						}
						// Ack confirma processamento bem-sucedido
						_ = msgCopy.Ack(false)
						slog.Info("Mensagem processada",
							slog.String("queue", consumerCopy.QueueName()),
							slog.String("worker", consumerCopy.WorkerName()),
							slog.Duration("duration", time.Since(start)),
						)
					}
				}
			}
		}(consumer)
	}

	// Aguarda sinal de shutdown
	<-sigCh
	slog.Info("Sinal de shutdown recebido. Finalizando workers...")
	cancel()
	close(tasks)
	wg.Wait()
	slog.Info("Go Notification & Chat Service finalizado com sucesso!")
}

// processNotification processa e simula o envio estruturado para o Loki/Grafana.
func processNotification(n *Notification) {
	start := time.Now()

	slog.Info("Processando envio de notificação",
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

	slog.Info("Notificação concluída",
		slog.String("user_id", n.UserID),
		slog.Duration("duration", time.Since(start)),
	)
}

// processChatMessage processa mensagens de chat/dúvidas e executa triagem (FAQ chatbot).
func processChatMessage(msg *ChatMessage) {
	start := time.Now()

	slog.Info("Processando mensagem de chat",
		slog.String("thread_id", msg.ThreadID),
		slog.String("sender_type", msg.SenderType),
		slog.String("channel", msg.Channel),
		slog.String("tenant_id", msg.TenantID),
	)

	// ── Chatbot de Triagem (FAQ de Suporte) ──
	// Apenas para mensagens de dúvidas (chat), não disputas
	if msg.Channel == "chat" {
		keywords := extractKeywords(msg.Content)
		if len(keywords) > 0 {
			slog.Info("Chatbot: palavras-chave detectadas",
				slog.String("thread_id", msg.ThreadID),
				slog.Any("keywords", keywords),
			)
			// Sugestões de FAQ são registradas para o Laravel exibir
			suggestFAQs(msg.ThreadID, keywords)
		}
	}

	slog.Info("Mensagem de chat processada",
		slog.String("thread_id", msg.ThreadID),
		slog.String("channel", msg.Channel),
		slog.Duration("duration", time.Since(start)),
	)
}

// FAQKeywords mapeia palavras-chave para categorias de FAQ.
var FAQKeywords = map[string]string{
	"frete":        "shipping",
	"entrega":      "shipping",
	"envio":        "shipping",
	"atraso":       "shipping",
	"prazo":        "shipping",
	"rastreio":     "shipping",
	"rastreamento": "shipping",
	"pagamento":    "payment",
	"pagar":        "payment",
	"boleto":       "payment",
	"cartão":       "payment",
	"cartao":       "payment",
	"pix":          "payment",
	"cancelar":     "cancellation",
	"cancelamento": "cancellation",
	"devolução":    "return",
	"devolucao":    "return",
	"reembolso":    "refund",
	"troca":        "return",
	"defeito":      "quality",
	"quebrado":     "quality",
	"danificado":   "quality",
	"material":     "product",
	"tamanho":      "product",
	"cor":          "product",
	"impressão":    "product",
	"impressao":    "product",
	"3d":           "product",
}

// FAQQuestions retorna perguntas sugeridas com base na categoria detectada.
var FAQQuestions = map[string][]string{
	"shipping": {
		"O vendedor paga o frete?",
		"Quantos dias a entrega pode atrasar?",
		"Como faço para rastrear meu pedido?",
		"Qual o prazo de entrega para minha região?",
	},
	"payment": {
		"Quais formas de pagamento são aceitas?",
		"Como gerar o boleto novamente?",
		"O pagamento via PIX é instantâneo?",
		"Posso parcelar no cartão de crédito?",
	},
	"cancellation": {
		"Como cancelar um pedido?",
		"Qual o prazo para cancelamento?",
		"O cancelamento gera reembolso automático?",
	},
	"return": {
		"Como solicitar a devolução de um produto?",
		"Quem paga o frete de devolução?",
		"Posso trocar por outro produto?",
	},
	"refund": {
		"Qual o prazo para o reembolso?",
		"O reembolso cai na mesma conta do pagamento?",
	},
	"quality": {
		"O produto chegou danificado, o que fazer?",
		"O produto veio diferente da foto, como proceder?",
		"Garantia de impressão 3D: qual o prazo?",
	},
	"product": {
		"Qual material é usado na impressão 3D?",
		"Posso escolher a cor do produto?",
		"Vocês imprimem em outros tamanhos?",
		"O que é PLA? Qual a diferença para ABS?",
	},
}

// extractKeywords extrai palavras-chave do conteúdo da mensagem.
func extractKeywords(content string) []string {
	detected := make(map[string]bool)
	for keyword, category := range FAQKeywords {
		if contains(content, keyword) && !detected[category] {
			detected[category] = true
		}
	}
	var categories []string
	for cat := range detected {
		categories = append(categories, cat)
	}
	return categories
}

// contains verifica se a string contém a substring (case-insensitive simple).
func contains(s, substr string) bool {
	return len(s) >= len(substr) && containsFold(s, substr)
}

func containsFold(s, substr string) bool {
	sLower := toLowerASCII(s)
	subLower := toLowerASCII(substr)
	for i := 0; i <= len(sLower)-len(subLower); i++ {
		if sLower[i:i+len(subLower)] == subLower {
			return true
		}
	}
	return false
}

func toLowerASCII(s string) string {
	b := make([]byte, len(s))
	for i := 0; i < len(s); i++ {
		c := s[i]
		if c >= 'A' && c <= 'Z' {
			c += 32
		}
		b[i] = c
	}
	return string(b)
}

// suggestFAQs registra sugestões de FAQ para o Laravel consumir.
// Em produção, isso publicaria de volta em uma fila ou cache Redis.
// Por enquanto, loga como structured log para o Grafana/Loki.
func suggestFAQs(threadID string, categories []string) {
	for _, cat := range categories {
		questions, ok := FAQQuestions[cat]
		if !ok {
			continue
		}
		for _, q := range questions {
			slog.Info("Chatbot FAQ Suggestion",
				slog.String("thread_id", threadID),
				slog.String("category", cat),
				slog.String("question", q),
			)
		}
	}
}