# frontend/Dockerfile
FROM nginx:alpine

# Remove default nginx static assets
RUN rm -rf /usr/share/nginx/html/*

# Copy static frontend files to nginx public folder
COPY . /usr/share/nginx/html

# Expose port 80
EXPOSE 80

# Start nginx (default CMD is fine for nginx image)