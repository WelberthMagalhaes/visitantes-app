#!/bin/bash

# Se PORT estiver definido (Render), configura Apache para usar essa porta
if [ ! -z "$PORT" ]; then
    echo "Listen $PORT" > /etc/apache2/ports.conf
    sed -i "s/80/$PORT/g" /etc/apache2/sites-available/000-default.conf
fi

# Inicia Apache
apache2-foreground