<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# Laravel 9 Rest API CRUD with Sanctum Example Tutorial

### Step 1: Download Laravel 9 App

```s
composer create-project --prefer-dist laravel/laravel nama_projek
```

### Step 2: Configure Database with App

Buka file **.env** dan ubah bagian DB_CONNECTION menjadi sqlite, sebagai alternatif agar tidak perlu menjalankan mysql dan buat database di dalamnya.

```
DB_CONNECTION=sqlite 
DB_HOST=127.0.0.1 
DB_PORT=3306
```

Buat file database **database.sqlite** di folder **database/**

## Konfigurasi dasar menggunakan Sanctum

### Step 3: Install Sanctum Auth

```s
composer require laravel/sanctum
```

Publish konfigurasi sanctum

```s
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

Uncomment Sanctum Middleware di file **app\Http\Kernel.php**

```php
...

'api' => [
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    'throttle:api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],

...
```

Jalankan migrasi tabel

```s
php artisan migrate
```

### Step 4: Sanctum Configuration

Tambahkan **HasApiTokens** di model **App/Models/User.php**, 

```php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable {

    use HasFactory, Notifiable, HasApiTokens;

    ...

```

Masih di file **App/Models/User.php** sebagai tambahan karena CRUD disini menggunakan relasi hasMany dari User ke Post maka tambahkan skrip di bawah

```php
    ...

    // Relationship hasMany: dengan Model Post 
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

}
```

### Step 5: Create Post Table and Model

Buat Post model dengan file migration

```s
php artisan make:model Post -m
```

Tambahkan field untuk tabel Post

```php
return new class extends Migration
{

    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('title');
            $table->longText('body');
            $table->string('photo');
            $table->timestamps();
        });
    }

    ...
```

Agar dapat memasukan data ke dalam tabel, ubah **app\Models\Post.php** dan tambahkan skrip di bawah

```php
protected $fillable = [
    'title', 'body', 'photo'
];
```

Jalankan migrasi untuk menambahkan tabel Post
```s
php artisan migrate:fresh
```

### Step 6: Install Intervention/Image

Instal paket untuk mengolah file berupa Image

```s
composer require intervention/image
```

Buka file **config/app.php** dan tambahkan **Intervention\Image\ImageServiceProvider::class** di providers

```php
'providers' =>[

    ...
    
    /*
     * Package Service Providers...
     */
    Intervention\Image\ImageServiceProvider::class,

    ...
],
```

### Step 7: Create Passport Auth and CRUD Controller

Buka file **app\Http\Controllers\Controller.php** buat fungsi sendResponse dan sendError, untuk kode lihat di sumber

```php
use Illuminate\Http\Response; // tambahkan librari Response

class Controller extends BaseController
{
    ... 

    public function sendResponse($result = "", $message = "")
    {
        // TODO: Success Response
    }

    public function sendError($error = "", $errorMessages = "", $code = Response::HTTP_BAD_REQUEST)
    {
        // TODO: Error Response
    }
}
```

Buat controller baru dengan perintah

```s
php artisan make:controller Api\AuthController
php artisan make:controller Api\PostController --resource
```

Buka file **app\Http\Controllers\Api\AuthController.php** buat fungsi login dan register, untuk kode lihat di sumber

```php
class AuthController extends Controller
{
    public function login(Request $request)
    {
        // TODO: Login
    }

    public function register(Request $request)
    {
        // TODO: Register
    }
}
```

Buka file **app\Http\Controllers\Api\PostController.php** hapus fungsi create dan edit karena tidak diperlukan untuk sumber API, untuk kode lihat di sumber

```php
class PostController extends Controller
{
    public function index()
    {
        // TODO: Get Posts
    }
    
    public function store(Request $request)
    {
        // TODO: Store Post
    }
    
    public function show($id)
    {
        // TODO: Show Post by {id}
    }
    
    public function update(Request $request, $id)
    {
        // TODO: Edit Post by {id}
    }
    
    public function destroy($id)
    {
        // TODO: Delete Post by {id}
    }
}
```

### Step 8: Create Auth and CRUD APIs Route

Ubah route di file **routes\web.php**

```php
Route::get('{any}', function () {
    return view('welcome');
})->where('any', '.*');
```

Lanjut tambahkan route baru di file **routes\api.php**

```php
Route::controller(AuthController::class)->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'login');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user', function (Request $request) {
        return $request->user();
    });

    Route::resource('post', PostController::class)->except(['create', 'edit']);
});
```

### Step 9: Run Project and Test API with Postman

Jalankan server laravel, akses url http://127.0.0.1:8000/api di Postman

```s
php artisan serve
```