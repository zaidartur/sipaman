
- add library spatie/laravel-csp
- add library league/flysystem-aws-s3-v3 "^3.0" untuk penyimpanan Minio
- konfigurasi CSP di folder config
- penambahan nonce di setiap tag script dan tag style
- pembuatan dockerfile
- pembuatan file pendukung untuk docker seperti web-server, dan konfigursai php di folder docker-config
- add route for Minio storage, hapus symlink untuk folder storage
- pembaruan beberapa script upload di folder Services untuk menyesuaikan dengan Minio
- menambahkan script di AppServiceProvider