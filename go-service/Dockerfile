# =========================================================================
# Go Notification Service — Ambiente de Runtime Ultra-leve
# =========================================================================
FROM scratch

# Copia os certificados CA
COPY --from=alpine:latest /etc/ssl/certs/ca-certificates.crt /etc/ssl/certs/

# Define a pasta de trabalho como /app (isso cria o diretório)
WORKDIR /app

# Copia o binário para dentro da pasta /app
# Certifique-se que no seu GitHub Actions o arquivo esteja na raiz do contexto de build
COPY notification-service .

# Usuário não-root
USER 65534:65534

# Aponta para o caminho correto (agora dentro de /app)
ENTRYPOINT ["/app/notification-service"]