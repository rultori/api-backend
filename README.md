<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# Prueba técnica Ixaya (API Backend) 

## ¿Cómo hacer funcionar el proyecto?

Ejecutar el siguiente comando para instalar todas las dependencias:
```sh
composer install
```

Lo siguiente es copiar el archivo .env.example a .env y modificar las variables de entorno correspondientes

Ejecutar el siguiente comando:
```sh
php artisan serve
```

## Consideraciones
- La API creada no tiene token para acceder a la información
- La API usada para la conversión de monedas tiene un límite de 100 peticiones por mes, de las cuales lleva alrededor de la mitad

## Endpoints creados
### Lista las ordenes filtradas por fecha
- /api/v1/orders/list_record
    * Recibe tres parametros de url, todos opcionales: start_date, end_date y currency. Ej: /api/v1/orders/list_record?end_date=2022-01-13&currency=USD
### Lista los 5 productos más vendidos filtrados por fecha 
- /api/v1/products/topselling
    * Recibe tres parametros de url, todos opcionales: start_date, end_date y sort. Ej: /api/v1/products/topselling?start_date=2022-01-15&sort=ASC

### Muestra el detalle de una orden específica

- /api/v1/orders/{id}
    * Recibe el parámetro id como se especifica, no es opcional. Ej: /api/v1/orders/12
