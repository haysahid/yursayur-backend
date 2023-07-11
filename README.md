<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Tentang YurSayur

Aplikasi YurSayur adalah sebuah aplikasi platform jual beli sayur secara online yang menghubungkan konsumen dengan pedagang sayur terdekat di daerahnya. Aplikasi YurSayur yang merupakan inovasi bisnis baru berbasis aplikasi android hadir untuk memudahkan masyarakat dalam melakukan pemesanan sayur.

## Cara Deploy API Backend

1. Instal Package

    `composer install`

2. Atur environment

    ```jsx
    APP_TIMEZONE='Asia/Jakarta'

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=nama_databasenya
    DB_USERNAME=user_databasenya
    DB_PASSWORD=password_databasenya

    FILESYSTEM_DISK=public

    ```

3. Buat symbolic link public storage

    `php artisan storage:link`

4. Jalankan migrasi database

    `php artisan migrate`

5. Jalankan Seeder

    `php artisan db:seed`

## API Endpoints

-   **User**
    | Method | Endpoint | Expectation | Code | Response Body | Result |
    | ------ | -------------- | ------------------------------------- | ---- | ------------------------------- | ------ |
    | POST | /auth/register | Bisa menambahkan data User | 201 | Access Token dan Satu data User | ✅ |
    | POST | /auth/login | Bisa log In dengan Email dan Password | 200 | Access Token dan Satu data User | ✅ |
    | GET | /user | Bisa mengambil data User | 200 | Satu data User | ✅ |
    | POST | /user | Bisa mengubah data User | 200 | Satu data User | ✅ |
    | POST | /auth/logout | Bisa Log Out dan revoke Access Token | 200 | Token revoke status | ✅ |

-   **Store**
    | Method | Endpoint | Expectation | Code | Response Body | Result |
    | ------ | ----------- | ------------------------------------------------------------------------ | ---- | ---------------- | ------ |
    | POST | /store | Bisa menambahkan Store | 201 | Satu data Store | ✅ |
    | GET | /store | Bisa mengambil daftar data Store (Query: search, page, limit) | 200 | Array data Store | ✅ |
    | GET | /store/{id} | Bisa mengambil salah satu data Store | 200 | Satu data Store | ✅ |
    | POST | /store/{id} | Bisa mengubah salah satu data Store (Form data tambahan: \_method=’PUT’) | 200 | Satu data Store | ✅ |
    | DELETE | /store/{id} | Bisa menghapus data salah satu Store | 200 | ID Store | ✅ |

-   **Product**
    | Method | Endpoint | Expectation | Code | Response Body | Result |
    | ------ | ------------- | ----------------------------------------------------------------------------------- | ---- | ------------------ | ------ |
    | POST | /product | Bisa menambahkan Product | 201 | Satu data Product | ✅ |
    | GET | /product | Bisa mengambil daftar data Product (Query: store_id, category, search, page, limit) | 200 | Array data Product | ✅ |
    | GET | /product/{id} | Bisa mengambil salah satu data Product | 200 | Satu data Product | ✅ |
    | POST | /product/{id} | Bisa mengubah salah satu data Product (Form data tambahan: \_method=’PUT’) | 200 | Satu data Product | ✅ |
    | DELETE | /product/{id} | Bisa menghapus data salah satu Product | 200 | ID Product | ✅ |

-   **Cart Item**
    | Method | Endpoint | Expectation | Code | Response Body | Result |
    | ------ | ---------- | ---------------------------------------- | ---- | -------------------- | ------ |
    | POST | /cart | Bisa menambahkan Cart Item | 201 | Satu data Cart Item | ✅ |
    | GET | /cart | Bisa mengambil daftar data Cart Item | 200 | Array data Cart Item | ✅ |
    | PUT | /cart/{id} | Bisa mengubah salah satu data Cart Item | 200 | Satu data Cart Item | ✅ |
    | DELETE | /cart/{id} | Bisa menghapus data salah satu Cart Item | 200 | ID Cart Item | ✅ |
-   **Order**
    | Method | Endpoint | Expectation | Code | Response Body | Result |
    | ------ | ----------- | --------------------------------------------------------------------- | ---- | ---------------- | ------ |
    | POST | /order | Bisa menambahkan Order | 201 | Satu data Order | ✅ |
    | GET | /order | Bisa mengambil daftar data Order (Query: payment_status, page, limit) | 200 | Array data Order | ✅ |
    | GET | /order/{id} | Bisa mengambil salah satu data Order | 200 | Satu data Order | ✅ |
    | PUT | /order/{id} | Bisa mengubah salah satu data Order | 200 | Satu data Order | ✅ |

## Kontributor

1. [Sahid Anwar](https://github.com/haysahid)
2. [Mico Yumna Ardhana](https://github.com/micoardhana090701)
3. Suha Jihan Majida
4. Ahmad Hermawan Bakhtiar Ikhsani
