# Tomodoro - API Документация

**Версия документа:** 1.0  
**Статус:** Утверждено  
**Дата:** 22.01.2026

---

## 1. Введение в API

### 1.1 Что такое API?

API (Application Programming Interface) — это интерфейс для программного взаимодействия с приложением Tomodoro. Позволяет:
- Получать и изменять данные о задачах
- Управлять сессиями Pomodoro
- Интегрировать Tomodoro с другими приложениями
- Создавать мобильные клиенты

### 1.2 Тип API

Приложение Tomodoro использует **RESTful API** с формате **JSON**.

### 1.3 Версия API

Текущая версия: **v1**  
Базовый URL: `https://api.tomodoro.example.com/api/v1`

---

## 2. Аутентификация

### 2.1 Типы аутентификации

Приложение поддерживает два типа аутентификации:

#### Способ 1: Token Authentication (Sanctum)

```bash
# Получение токена при входе
curl -X POST https://api.tomodoro.example.com/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password123"
  }'

# Ответ:
{
  "token": "1|AbCdEfGhIjKlMnOpQrStUvWxYz",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com"
  }
}

# Использование токена в запросах
curl -X GET https://api.tomodoro.example.com/api/v1/tasks \
  -H "Authorization: Bearer 1|AbCdEfGhIjKlMnOpQrStUvWxYz" \
  -H "Accept: application/json"
```

#### Способ 2: Session Authentication

```bash
# Аутентификация через сессию (cookies)
curl -c cookies.txt -X POST https://api.tomodoro.example.com/login \
  -d 'email=user@example.com&password=password123'

# Использование сессии в запросах
curl -b cookies.txt -X GET https://api.tomodoro.example.com/api/v1/tasks
```

### 2.2 Регистрация нового пользователя

```bash
POST /api/v1/register
Content-Type: application/json

{
  "name": "Jane Smith",
  "email": "jane@example.com",
  "password": "SecurePassword123",
  "password_confirmation": "SecurePassword123"
}

# Ответ 201 Created:
{
  "token": "1|XxXxXxXxXxXxXxXxXxXxXxXxXx",
  "user": {
    "id": 2,
    "name": "Jane Smith",
    "email": "jane@example.com",
    "created_at": "2026-01-22T10:00:00Z"
  }
}
```

### 2.3 Выход из приложения

```bash
POST /api/v1/logout
Authorization: Bearer {token}

# Ответ 200 OK:
{
  "message": "Logged out successfully"
}
```

---

## 3. Структура API ответов

### 3.1 Успешный ответ

```json
{
  "data": {
    "id": 1,
    "name": "Buy groceries",
    "status": "pending",
    ...
  },
  "message": "Task retrieved successfully",
  "status": 200
}
```

### 3.2 Ошибка

```json
{
  "message": "Validation error",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  },
  "status": 422
}
```

### 3.3 Коды ответов

| Код | Значение | Описание |
|-----|----------|---------|
| 200 | OK | Запрос успешен |
| 201 | Created | Ресурс создан |
| 204 | No Content | Успешно удалено/обновлено |
| 400 | Bad Request | Неправильный запрос |
| 401 | Unauthorized | Требуется аутентификация |
| 403 | Forbidden | Доступ запрещён |
| 404 | Not Found | Ресурс не найден |
| 422 | Unprocessable Entity | Ошибка валидации |
| 429 | Too Many Requests | Слишком много запросов |
| 500 | Server Error | Ошибка сервера |

---

## 4. Управление задачами

### 4.1 Получить все задачи

```bash
GET /api/v1/tasks
Authorization: Bearer {token}
Accept: application/json

# Параметры запроса (опциональные):
# ?status=pending&category=work&sort=-created_at&per_page=20&page=1

# Ответ 200 OK:
{
  "data": [
    {
      "id": 1,
      "title": "Buy groceries",
      "description": "Milk, bread, eggs",
      "status": "pending",
      "category": "personal",
      "priority": "high",
      "due_date": "2026-01-25",
      "created_at": "2026-01-22T10:00:00Z",
      "updated_at": "2026-01-22T10:00:00Z",
      "tags": ["shopping", "food"]
    },
    ...
  ],
  "meta": {
    "total": 15,
    "per_page": 20,
    "current_page": 1,
    "last_page": 1
  }
}
```

### 4.2 Получить одну задачу

```bash
GET /api/v1/tasks/{id}
Authorization: Bearer {token}
Accept: application/json

# Ответ 200 OK:
{
  "data": {
    "id": 1,
    "title": "Buy groceries",
    "description": "Milk, bread, eggs",
    "status": "pending",
    "category": "personal",
    "priority": "high",
    "due_date": "2026-01-25",
    "pomodoro_estimate": 2,
    "created_at": "2026-01-22T10:00:00Z",
    "updated_at": "2026-01-22T10:00:00Z",
    "tags": ["shopping", "food"]
  }
}
```

### 4.3 Создать задачу

```bash
POST /api/v1/tasks
Authorization: Bearer {token}
Content-Type: application/json

{
  "title": "Write report",
  "description": "Final quarterly report",
  "category": "work",
  "priority": "high",
  "due_date": "2026-01-30",
  "pomodoro_estimate": 3,
  "tags": ["report", "quarterly"]
}

# Ответ 201 Created:
{
  "data": {
    "id": 16,
    "title": "Write report",
    "description": "Final quarterly report",
    "status": "pending",
    "category": "work",
    "priority": "high",
    "due_date": "2026-01-30",
    "pomodoro_estimate": 3,
    "created_at": "2026-01-22T11:30:00Z",
    "updated_at": "2026-01-22T11:30:00Z",
    "tags": ["report", "quarterly"]
  }
}
```

### 4.4 Обновить задачу

```bash
PUT /api/v1/tasks/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "title": "Write report (UPDATED)",
  "status": "in_progress",
  "priority": "critical"
}

# Ответ 200 OK:
{
  "data": {
    "id": 16,
    "title": "Write report (UPDATED)",
    "status": "in_progress",
    "priority": "critical",
    ...
  },
  "message": "Task updated successfully"
}
```

### 4.5 Удалить задачу

```bash
DELETE /api/v1/tasks/{id}
Authorization: Bearer {token}

# Ответ 204 No Content (без тела ответа)
```

### 4.6 Фильтрация и сортировка

```bash
# Получить только завершённые задачи
GET /api/v1/tasks?status=completed

# Получить задачи категории "work"
GET /api/v1/tasks?category=work

# Получить задачи с высоким приоритетом
GET /api/v1/tasks?priority=high

# Сортировка по дате создания (новые сначала)
GET /api/v1/tasks?sort=-created_at

# Сортировка по сроку (ближайшие сначала)
GET /api/v1/tasks?sort=due_date

# Комбинированный фильтр
GET /api/v1/tasks?status=pending&category=work&priority=high&sort=due_date&per_page=10
```

---

## 5. Управление Pomodoro сессиями

### 5.1 Получить все сессии

```bash
GET /api/v1/pomodoro/sessions
Authorization: Bearer {token}

# Ответ 200 OK:
{
  "data": [
    {
      "id": 1,
      "task_id": 5,
      "duration_minutes": 25,
      "status": "completed",
      "started_at": "2026-01-22T09:00:00Z",
      "ended_at": "2026-01-22T09:25:00Z",
      "break_type": "short"
    },
    ...
  ]
}
```

### 5.2 Запустить новую сессию

```bash
POST /api/v1/pomodoro/sessions
Authorization: Bearer {token}
Content-Type: application/json

{
  "task_id": 5,
  "duration_minutes": 25
}

# Ответ 201 Created:
{
  "data": {
    "id": 42,
    "task_id": 5,
    "duration_minutes": 25,
    "status": "active",
    "started_at": "2026-01-22T14:00:00Z",
    "ended_at": null,
    "break_type": null
  }
}
```

### 5.3 Завершить сессию

```bash
PUT /api/v1/pomodoro/sessions/{id}/complete
Authorization: Bearer {token}

# Ответ 200 OK:
{
  "data": {
    "id": 42,
    "task_id": 5,
    "duration_minutes": 25,
    "status": "completed",
    "started_at": "2026-01-22T14:00:00Z",
    "ended_at": "2026-01-22T14:25:00Z",
    "break_type": "short"
  }
}
```

### 5.4 Получить статистику

```bash
GET /api/v1/pomodoro/statistics?date=2026-01-22
Authorization: Bearer {token}

# Ответ 200 OK:
{
  "data": {
    "date": "2026-01-22",
    "total_sessions": 8,
    "total_minutes": 200,
    "tasks_completed": 5,
    "average_per_task": 2.5,
    "break_time": 45,
    "sessions_by_hour": {
      "09": 2,
      "10": 0,
      "11": 0,
      "12": 0,
      "14": 3,
      "15": 2,
      "16": 1
    }
  }
}
```

---

## 6. Управление категориями и тегами

### 6.1 Получить все категории

```bash
GET /api/v1/categories
Authorization: Bearer {token}

# Ответ 200 OK:
{
  "data": [
    {
      "id": 1,
      "name": "work",
      "description": "Work-related tasks",
      "color": "#FF5733",
      "count": 12
    },
    {
      "id": 2,
      "name": "personal",
      "description": "Personal tasks",
      "color": "#33FF57",
      "count": 8
    }
  ]
}
```

### 6.2 Получить все теги

```bash
GET /api/v1/tags
Authorization: Bearer {token}

# Ответ 200 OK:
{
  "data": [
    {
      "id": 1,
      "name": "urgent",
      "count": 5
    },
    {
      "id": 2,
      "name": "important",
      "count": 10
    }
  ]
}
```

---

## 7. Профиль пользователя

### 7.1 Получить профиль

```bash
GET /api/v1/user/profile
Authorization: Bearer {token}

# Ответ 200 OK:
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "avatar_url": "https://api.tomodoro.example.com/storage/avatars/user1.jpg",
    "timezone": "Europe/Moscow",
    "locale": "ru",
    "created_at": "2026-01-01T00:00:00Z"
  }
}
```

### 7.2 Обновить профиль

```bash
PUT /api/v1/user/profile
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Jane Doe",
  "timezone": "Europe/London",
  "locale": "en"
}

# Ответ 200 OK
```

### 7.3 Изменить пароль

```bash
POST /api/v1/user/password
Authorization: Bearer {token}
Content-Type: application/json

{
  "current_password": "old_password123",
  "password": "new_password123",
  "password_confirmation": "new_password123"
}

# Ответ 200 OK:
{
  "message": "Password changed successfully"
}
```

---

## 8. Примеры использования

### 8.1 JavaScript (Fetch API)

```javascript
// Получение всех задач
async function getTasks() {
  const response = await fetch('https://api.tomodoro.example.com/api/v1/tasks', {
    method: 'GET',
    headers: {
      'Authorization': 'Bearer ' + localStorage.getItem('token'),
      'Accept': 'application/json'
    }
  });
  return await response.json();
}

// Создание новой задачи
async function createTask(title, description) {
  const response = await fetch('https://api.tomodoro.example.com/api/v1/tasks', {
    method: 'POST',
    headers: {
      'Authorization': 'Bearer ' + localStorage.getItem('token'),
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      title: title,
      description: description,
      category: 'work',
      priority: 'high'
    })
  });
  return await response.json();
}
```

### 8.2 Python (Requests)

```python
import requests

API_URL = 'https://api.tomodoro.example.com/api/v1'
TOKEN = 'your_token_here'

headers = {
    'Authorization': f'Bearer {TOKEN}',
    'Accept': 'application/json'
}

# Получить все задачи
response = requests.get(f'{API_URL}/tasks', headers=headers)
tasks = response.json()

# Создать задачу
task_data = {
    'title': 'New Task',
    'description': 'Task description',
    'category': 'work',
    'priority': 'high'
}
response = requests.post(
    f'{API_URL}/tasks',
    headers=headers,
    json=task_data
)
new_task = response.json()
```

### 8.3 cURL примеры

```bash
# Аутентификация
curl -X POST https://api.tomodoro.example.com/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password123"}'

# Получить задачи
curl -X GET https://api.tomodoro.example.com/api/v1/tasks \
  -H "Authorization: Bearer TOKEN" \
  -H "Accept: application/json"

# Создать задачу
curl -X POST https://api.tomodoro.example.com/api/v1/tasks \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"New Task","category":"work","priority":"high"}'
```

---

## 9. Лучшие практики

### 9.1 Безопасность

- ✓ Всегда используйте HTTPS в production
- ✓ Храните токены в защищённом месте (не в localStorage для критичных операций)
- ✓ Регулярно обновляйте пароли
- ✓ Используйте CORS для контроля доступа
- ✗ Не отправляйте токены в URL

### 9.2 Производительность

- Используйте пагинацию (`per_page`, `page`)
- Кэшируйте статические данные (категории, теги)
- Используйте фильтры на сервере, а не на клиенте
- Ограничивайте размер ответов

### 9.3 Обработка ошибок

```javascript
try {
  const response = await fetch(url, options);
  if (!response.ok) {
    const error = await response.json();
    console.error('API Error:', error.message);
  }
  return await response.json();
} catch (error) {
  console.error('Network Error:', error);
}
```

---

## 10. Rate Limiting

API использует rate limiting для предотвращения злоупотребления:

- **Лимит**: 60 запросов в минуту для одного пользователя
- **Заголовки ответа**:
  - `X-RateLimit-Limit: 60`
  - `X-RateLimit-Remaining: 45`
  - `X-RateLimit-Reset: 1642862400`

При превышении лимита получите ответ **429 Too Many Requests**.

---

**Документ соответствует ГОСТ 19.506-79 "Описание программы"**

Дата последнего обновления: 22.01.2026
