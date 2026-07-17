# =========================================================================
# Go Notification Service — Ambiente de Runtime Ultra-leve (Binário Pré-compilado)
# =========================================================================
FROM scratch

# Copia os certificados CA (Caso você precise bater em APIs externas HTTPS)
# Nota: Como o build ocorre no GitHub, você pode copiar os certificados da imagem do Alpine no passo do Actions se precisar, 
# mas se o binário for estático puro, o scratch executa direto. 
# Para garantir os certs sem precisar compilar no host, usamos a própria base do alpine apenas para extrair os certs em segundos:
COPY --from=alpine:latest /etc/ssl/certs/ca-certificates.crt /etc/ssl/certs/

WORKDIR /

# O GitHub Actions vai colocar o binário pronto dentro da pasta go-service
# O Docker apenas copia ele para dentro da imagem estática
COPY notification-service /notification-service

# Mantém o seu padrão excelente de usuário não-root (segurança)
USER 65534:65534

ENTRYPOINT ["/notification-service"]