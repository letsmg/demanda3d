# =============================================
# Go Notification Service — Multi-stage build
# =============================================

# ---- Stage 1: Build ----
FROM golang:1.25-alpine AS builder

RUN apk add --no-cache git ca-certificates

WORKDIR /app

# Copia go.mod e go.sum primeiro (cache de dependências)
COPY go.mod go.sum ./
RUN go mod download

# Copia o código fonte e compila
COPY . .
RUN CGO_ENABLED=0 GOOS=linux GOARCH=amd64 go build \
    -ldflags="-s -w" \
    -trimpath \
    -o /app/notification-service .

# ---- Stage 2: Runtime mínimo ----
FROM scratch

# Copia certificados CA para TLS (se necessário para APIs externas)
COPY --from=builder /etc/ssl/certs/ca-certificates.crt /etc/ssl/certs/

# Copia binário compilado
COPY --from=builder /app/notification-service /notification-service

# Usuário não-root (segurança)
USER 65534:65534

ENTRYPOINT ["/notification-service"]