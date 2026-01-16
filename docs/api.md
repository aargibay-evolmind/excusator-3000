# Excusator 3000 - API Documentation

Base URL: `http://back.executor.local/api`

## Public Endpoints

### Get Categories (Wheel)
Retrieves a list of "Valid" categories for the wheel. 
*A category is valid if it is Active, not deleted, and has at least 5 active excuses.*

- **URL**: `/wheel/categories`
- **Method**: `GET`
- **Response**: `200 OK`
    ```json
    [
        {
            "id": 1,
            "name": "Category Name",
            "active": true,
            "excuseCount": 10
        }
    ]
    ```

### Get Random Excuse
Retrieves a random excuse for a specific category.

- **URL**: `/wheel/excuse/{categoryId}`
- **Method**: `GET`
- **Response**: `200 OK`
    ```json
    {
        "id": 55,
        "content": "My dog ate my homework",
        "categoryId": 1
    }
    ```

## Admin Endpoints

### Categories

#### List Categories
- **URL**: `/admin/categories`
- **Method**: `GET`
- **Response**: `200 OK` (Array of Category objects)

#### Get Category
- **URL**: `/admin/categories/{id}`
- **Method**: `GET`
- **Response**: `200 OK`

#### Create Category
- **URL**: `/admin/categories`
- **Method**: `POST`
- **Body**:
    ```json
    {
        "name": "New Category",
        "active": true
    }
    ```
- **Response**: `201 Created`

#### Update Category
- **URL**: `/admin/categories/{id}`
- **Method**: `PUT`
- **Body**: Same as Create.
- **Response**: `200 OK`

#### Delete Category (Soft Delete)
- **URL**: `/admin/categories/{id}`
- **Method**: `DELETE`
- **Response**: `204 No Content`

### Excuses

#### List Excuses
- **URL**: `/admin/excuses`
- **Method**: `GET`
- **Response**: `200 OK` (Array of Excuse objects)

#### Get Excuse
- **URL**: `/admin/excuses/{id}`
- **Method**: `GET`
- **Response**: `200 OK`

#### Create Excuse
- **URL**: `/admin/excuses`
- **Method**: `POST`
- **Body**:
    ```json
    {
        "content": "This is an excuse",
        "categoryId": 1
    }
    ```
- **Response**: `201 Created`

#### Update Excuse
- **URL**: `/admin/excuses/{id}`
- **Method**: `PUT`
- **Body**: Same as Create.
- **Response**: `200 OK`

#### Delete Excuse (Soft Delete)
- **URL**: `/admin/excuses/{id}`
- **Method**: `DELETE`
- **Response**: `204 No Content`
