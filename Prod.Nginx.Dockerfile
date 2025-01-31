# Use the official PHP image as a base image
FROM nginx:alpine

# Atualiza e instala os pacotes necessários
# certbot certbot-nginx são os responsáveis pela geração de HTTPS
RUN apk update && apk upgrade && \
    apk --update add logrotate openssl bash && \
    apk add --no-cache certbot certbot-nginx 

# Limpeza: Remove pacotes não utilizados para reduzir o tamanho da imagem
RUN apk del --no-cache
    
# Inicia o NGINX quando o contêiner é executado
CMD ["nginx", "-g", "daemon off;"]
