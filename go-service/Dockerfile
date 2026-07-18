# =========================================================================
# Estágio 1: Build (Compila o binário dentro do Docker)
# =========================================================================
FROM golang:1.22-alpine AS builder

# Define o diretório de trabalho do build
WORKDIR /app

# Copia todo o código fonte para o container de build
COPY . .

# Compila o binário de forma estática (garante que rode no scratch)
RUN CGO_ENABLED=0 GOOS=linux GOARCH=amd64 go build -o notification-service .

# =========================================================================
# Estágio 2: Runtime (Imagem final super leve)
# =========================================================================
FROM scratch

# Copia os certificados CA
COPY --from=alpine:latest /etc/ssl/certs/ca-certificates.crt /etc/ssl/certs/

# Define o diretório final
WORKDIR /app

# Copia APENAS o binário compilado do estágio anterior para o diretório /app
COPY --from=builder /app/notification-service /app/notification-service

# Usuário não-root
USER 65534:65534

# Executa o binário
ENTRYPOINT ["/app/notification-service"]