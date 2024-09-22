# Library Management System

This is a Laravel-based RESTful API for managing authors and books. This project includes basic CRUD operations, caching, and pagination.

## Table of Contents
- [Requirements](#requirements)
- [Installation](#installation)
- [API Endpoints](#api-endpoints)
  - [Authors](#authors)
  - [Books](#books)
- [Testing](#testing)

## Requirements

- PHP 8.2 or higher
- Composer
- MySQL database
- Cache use default laravel

## Installation

1. Clone the repository:

   ```bash
   git clone https://github.com/yosrio/Library-Management-System.git
   ```

2. Navigate to the project directory:
   ```bash
   cd your-repo-name
   ```

3. Install the dependencies:
   ```bash
   composer install
   ```

4. Copy the .env.example file to .env and configure your environment:
   ```bash
   cp .env.example .env
   ```
   Set the necessary environment variables, including database credentials (using mysql):
   ```bash
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=your-database-name
   DB_USERNAME=your-username
   DB_PASSWORD=your-password
   ```

5. Generate an application key:
   ```bash
   php artisan key:generate
   ```

6. Run the database migrations:
   ```bash
   php artisan migrate
   ```

7. Serve the application:
   ```bash
   php artisan serve
   ```

## API Endpoints

### Authors
1. GET ```https://baseurl/authors``` (Get List Author)
<br>Example Response:
   ```bash
    [
        {
            "id": 1,
            "name": "Author Name",
            "bio": "Biography of the author",
            "birth_date": "1970-01-01"
        }
    ]
   ```

2. GET ```https://baseurl/authors/{id}``` (Get Specific Author) <br>
Example Url: ```https://baseurl/authors/1```<br>
Example Response:
   ```bash
    {
        "id": 1,
        "name": "Author Name",
        "bio": "Biography of the author",
        "birth_date": "1970-01-01"
    }
   ```

3. POST ```https://baseurl/authors``` (Add Author) <br>
Payload:
   ```bash
    {
        "name": "New Author",
        "bio": "Short bio",
        "birth_date": "1990-05-12"
    }
   ```
   Example Response:
   ```bash
    {
        "id": 2,
        "name": "New Author",
        "bio": "Short bio",
        "birth_date": "1990-05-12"
    }
   ```

4. PUT ```https://baseurl/authors/{id}``` (Update Author) <br>
Example Url: ```https://baseurl/authors/1```<br>
Payload:
   ```bash
    {
        "name": "Updated Author",
        "bio": "Updated bio",
        "birth_date": "1980-10-05"
    }
   ```
   Example Response:
   ```bash
    {
        "id": 2,
        "name": "Updated Author",
        "bio": "Updated bio",
        "birth_date": "1980-10-05"
    }
   ```

5. DELETE ```https://baseurl/authors/{id}``` (Delete Author) <br>
Example Url: ```https://baseurl/authors/1```<br>
Example Status Code:```204 No Content```

7. GET ```https://baseurl/authors/{id}/books``` (Get List Book by Specific Author) <br>
Example Url: ```https://baseurl/authors/1/books```<br>
<br>Example Response:
   ```bash
    [
        {
            "id": 1,
            "title": "Book Title",
            "description": "Book description",
            "publish_date": "2023-01-01",
            "author_id": 1
        },
        {
            "id": 2,
            "title": "Another Book",
            "description": "Another description",
            "publish_date": "2023-06-12",
            "author_id": 1
        }
    ]
   ```

### Books
1. GET ```https://baseurl/books``` (Get List Book)
<br>Example Response:
   ```bash
    [
        {
            "id": 1,
            "title": "Book Title",
            "description": "Book description",
            "publish_date": "2023-01-01",
            "author_id": 1
        }
    ]
   ```

2. GET ```https://baseurl/books/{id}``` (Get Specific Book) <br>
Example Url: ```https://baseurl/books/1```<br>
Example Response:
   ```bash
    {
        "id": 1,
        "title": "Book Title",
        "description": "Book description",
        "publish_date": "2023-01-01",
        "author_id": 1
    }
   ```

3. POST ```https://baseurl/books``` (Add Book) <br>
Payload:
   ```bash
    {
        "title": "New Book",
        "description": "Description of the book",
        "publish_date": "2023-05-15",
        "author_id": 1
    }
   ```
   Example Response:
   ```bash
    {
        "id": 2,
        "title": "New Book",
        "description": "Description of the book",
        "publish_date": "2023-05-15",
        "author_id": 1
    }
   ```

4. PUT ```https://baseurl/books/{id}``` (Update Book) <br>
Example Url: ```https://baseurl/books/1```<br>
Payload:
   ```bash
    {
        "title": "Updated Book",
        "description": "Updated description",
        "publish_date": "2023-07-12",
        "author_id": 1
    }
   ```
   Example Response:
   ```bash
    {
        "id": 2,
        "title": "Updated Book",
        "description": "Updated description",
        "publish_date": "2023-07-12",
        "author_id": 1
    }
   ```

5. DELETE ```https://baseurl/books/{id}``` (Delete Book) <br>
Example Url: ```https://baseurl/books/1```<br>
Example Status Code: ```204 No Content```

## Testing
To run the tests, use the following command:
   ```bash
    php artisan test --filter BookControllerTest
   ```
   ```bash
    php artisan test --filter AuthorControllerTest
   ```
