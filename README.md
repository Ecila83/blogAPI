# blogAPI
# Publication Management API

This API allows you to manage publications, such as blog articles or messages. It exposes endpoints for retrieving, creating, updating, and deleting publications.

## Features

The following features are available in this API:

- Retrieve all publications
- Retrieve a publication by its identifier
- Create a new publication
- Update an existing publication
- Delete a publication

## Installation

Before using this API, ensure you have the following prerequisites installed on your system:

- PHP (>=7.0)
- Composer

### Composer

Composer is a dependency manager for PHP. If you haven't installed Composer yet, you can download and install it from the [official website](https://getcomposer.org/download/).

### dotenv

dotenv is a PHP package that loads environment variables from a `.env` file into `getenv()`, `$_ENV`, and `$_SERVER`. To install dotenv, run the following command in your project directory:

```bash
composer require vlucas/phpdotenv

## Endpoints

The endpoints available in this API are as follows:

- **GET /api/v1/posts**: Retrieves all publications.
- **GET /api/v1/post/:id**: Retrieves a publication by its identifier.
- **POST /api/v1/post**: Creates a new publication.
- **PUT /api/v1/post/:id**: Updates an existing publication.
- **DELETE /api/v1/post/:id**: Deletes a publication.

## Usage

### Retrieve all publications

GET /api/v1/posts

### Retrieve a publication by its identifier

GET /api/v1/post/123

### Create a new publication

POST /api/v1/post
Content-Type: application/json

{
    "title": "New article",
    "body": "Article content...",
    "author": "Article author"
}

### Update an existing publication

PUT /api/v1/post/123
Content-Type: application/json

{
    "title": "Updated article",
    "body": "Updated article content...",
    "author": "New article author"
}

### Delete a publication 

DELETE /api/v1/post/123

## Responses

API responses are in JSON format and follow standard HTTP conventions. HTTP status codes and messages are used to indicate the result of the operation.

Example of successful response (status code 200):

{
    "status": 200,
    "message": "OK",
    "data": [
        {
            "id": 1,
            "title": "Première publication",
            "body": "Ceci est le premier post de test.",
            "author": "Alice",
            "created_at": "2022-03-25 10:00:00",
            "updated_at": "2022-03-25 10:00:00"
        }
   ]
}   

Example of error response(statuts code 404):

{
    "message": "Publication not found."
}

## Author

This API was developed by Alice MARIQUE.

