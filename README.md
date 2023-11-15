# Image Proxy Assignment - PHP

This is a PHP script that serves as an image proxy. It fetches images from an AWS S3 bucket, modifies them based on query parameters, and serves them to the client. Additionally, it has the capability to cache the images..

I've made this repository with optional Docker integration, to make the testing process for this assignment more streamlined. It's also a fun way for me to demonstrate my skills in setting up development environments. Just a heads up, I'm using the latest Docker version (24.0.6) for this setup.

## Prerequisites

- PHP 7.2 or higher
- Composer
- Docker (optional)

## Setup

### Without Docker

1. Clone this repository and navigate to the project directory.

```bash
git clone https://github.com/erocha3/image-proxy-php.git
cd image-proxy-php
```

2. Install the dependencies using Composer.

```bash
composer install
```

3. Copy the `.env.example` file to a new file named `.env` and replace the placeholders with your actual AWS credentials and S3 bucket details.

```bash
cp .env.example .env
```
Replace `your_access_key`, `your_secret_key`, `your_bucket_name`, `your_folder_name` with your actual values.

4. Start the PHP built-in server.

```bash
php -S localhost:8000
```

Now, you can access the PHP script at `http://localhost:8000/my-images/your_image_name?width=500&type=jpg`. Replace `your_image_name` with the name of an image in your S3 bucket.


### With Docker

1. Clone this repository and navigate to the project directory.

```bash
git clone https://github.com/erocha3/image-proxy-php.git
cd image-proxy-php
```

2. Copy the `.env.example` file to a new file named `.env` and replace the placeholders with your actual AWS credentials and S3 bucket details.

```bash
cp .env.example .env
```

Replace `your_access_key`, `your_secret_key`, `your_bucket_name`, `your_folder_name` with your actual values.

3. Build and run the Docker container:

```bash
docker-compose up
```

Now, you can access the PHP script at `http://localhost:8000/my-images/your_image_name?width=500&type=jpg`. Replace `your_image_name` with the name of an image in your S3 bucket.

## Usage

You can modify the served image by changing the query parameters in the URL:

- `width`: The width of the image in pixels.
- `height`: The height of the image in pixels.
- `type`: The image format (e.g., `jpg`, `png`, `webp`, `jpeg`).