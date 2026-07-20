# =========================================================================
# Estágio 1: Build (Compila o binário dentro do Docker)
# =========================================================================
FROM golang:1.25-alpine AS builder

# Define o diretório de trabalho do build
WORKDIR /app

# 1. Copia PRIMEIRO apenas os arquivos de controle de dependências
COPY go.mod go.sum ./
RUN go mod download

# 2. Copia o restante do código fonte depois (aproveita o cache)
COPY . .

# 3. Compila limitando o uso de recursos para não estourar a RAM/CPU da VPS (evita Broken pipe):
# - GOMAXPROCS=1: Força o compilador a usar apenas 1 núcleo, evitando picos repentinos
# - -ldflags="-s -w": Remove informações de debug, deixando o build mais leve
RUN CGO_ENABLED=0 GOOS=linux GOARCH=amd64 GOMAXPROCS=1 go build -ldflags="-s -w" -o notification-service .

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
ENTRYPOINT ["/app/notification-service"]# =========================================================================
# Estágio 1: Build (Compila o binário dentro do Docker)
# =========================================================================
FROM golang:1.25-alpine AS builder

# Define o diretório de trabalho do build
WORKDIR /app

# 1. Copia PRIMEIRO apenas os arquivos de controle de dependências
COPY go.mod go.sum ./
RUN go mod download

# 2. Copia o restante do código fonte depois (aproveita o cache)
COPY . .

# 3. Compila limitando o uso de recursos para não estourar a RAM/CPU da VPS (evita Broken pipe):
# - GOMAXPROCS=1: Força o compilador a usar apenas 1 núcleo, evitando picos repentinos
# - -ldflags="-s -w": Remove informações de debug, deixando o build mais leve
RUN CGO_ENABLED=0 GOOS=linux GOARCH=amd64 GOMAXPROCS=1 go build -ldflags="-s -w" -o notification-service .

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