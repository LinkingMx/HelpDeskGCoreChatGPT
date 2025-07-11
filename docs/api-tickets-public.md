# API Pública de Tickets - Documentación

## Descripción

API REST pública para consultar información completa de tickets, incluyendo toda la conversación y metadatos.

## Endpoints Disponibles

### 1. Obtener Ticket por Número

**GET** `/api/tickets/{ticketNumber}`
**GET** `/api/public/tickets/{ticketNumber}` (alternativo)

-   **Método:** GET
-   **Autenticación:** No requerida (público)
-   **Parámetros:**
    -   `ticketNumber` (int): Número/ID del ticket

## Ejemplos de Uso

### cURL

```bash
# Obtener ticket #123
curl -X GET "http://tu-dominio.com/api/tickets/123"

# Con el endpoint alternativo
curl -X GET "http://tu-dominio.com/api/public/tickets/123"
```

### JavaScript (Fetch)

```javascript
// Obtener ticket
async function getTicket(ticketNumber) {
    try {
        const response = await fetch(`/api/tickets/${ticketNumber}`);
        const data = await response.json();

        if (data.success) {
            console.log("Ticket encontrado:", data.data);
        } else {
            console.error("Error:", data.message);
        }
    } catch (error) {
        console.error("Error de conexión:", error);
    }
}

// Uso
getTicket(123);
```

### PHP

```php
// Obtener ticket usando cURL
function getTicket($ticketNumber) {
    $url = "http://tu-dominio.com/api/tickets/" . $ticketNumber;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return json_decode($response, true);
}

// Uso
$ticketData = getTicket(123);
if ($ticketData['success']) {
    echo "Ticket: " . $ticketData['data']['subject'];
}
```

## Estructura de Respuesta

### Respuesta Exitosa (200)

```json
{
    "success": true,
    "message": "Ticket encontrado",
    "data": {
        "id": 123,
        "subject": "Problema con el sistema",
        "description": "Descripción detallada del problema...",
        "priority": 1,
        "priority_text": "Alta",
        "created_at": "2025-07-03 10:30:00",
        "updated_at": "2025-07-03 15:45:00",
        "created_at_human": "hace 2 horas",
        "updated_at_human": "hace 30 minutos",

        "client": {
            "id": 1,
            "name": "Empresa ABC",
            "contact_email": "contacto@empresa.com",
            "contact_phone": "+1234567890"
        },

        "department": {
            "id": 1,
            "name": "Soporte Técnico",
            "description": "Departamento de soporte"
        },

        "category": {
            "id": 1,
            "name": "Hardware",
            "icon": "heroicon-o-computer-desktop",
            "color": "blue",
            "time": 24
        },

        "status": {
            "id": 1,
            "name": "En Progreso",
            "color": "warning",
            "description": "Ticket siendo trabajado"
        },

        "agent": {
            "id": 5,
            "name": "Juan Pérez",
            "email": "juan@empresa.com",
            "position": "Técnico Senior"
        },

        "creator": {
            "id": 10,
            "name": "María García",
            "email": "maria@cliente.com"
        },

        "sla": {
            "defined": true,
            "sla_hours": 24,
            "elapsed_hours": 5,
            "remaining_hours": 19,
            "overdue_hours": null,
            "percentage_used": 20.83,
            "status": "En tiempo"
        },

        "conversation": [
            {
                "id": 1,
                "message": "Mensaje inicial del ticket...",
                "created_at": "2025-07-03 10:30:00",
                "created_at_human": "hace 2 horas",
                "user": {
                    "id": 10,
                    "name": "María García",
                    "email": "maria@cliente.com",
                    "position": null
                }
            },
            {
                "id": 2,
                "message": "Respuesta del agente...",
                "created_at": "2025-07-03 11:15:00",
                "created_at_human": "hace 1 hora",
                "user": {
                    "id": 5,
                    "name": "Juan Pérez",
                    "email": "juan@empresa.com",
                    "position": "Técnico Senior"
                }
            }
        ],

        "attachments": [
            {
                "id": 1,
                "file_name": "screenshot.png",
                "file_path": "/uploads/tickets/123/screenshot.png",
                "file_size": 245760,
                "mime_type": "image/png",
                "created_at": "2025-07-03 10:30:00"
            }
        ],

        "statistics": {
            "total_comments": 5,
            "time_elapsed_hours": 5,
            "time_elapsed_human": "hace 5 horas",
            "last_activity": "hace 30 minutos"
        }
    }
}
```

### Respuesta de Error (404)

```json
{
    "success": false,
    "message": "Ticket no encontrado",
    "data": null
}
```

### Respuesta de Error (500)

```json
{
    "success": false,
    "message": "Error interno del servidor",
    "error": "Mensaje de error detallado (solo en modo debug)"
}
```

## Códigos de Estado HTTP

-   **200 OK**: Ticket encontrado y devuelto exitosamente
-   **404 Not Found**: Ticket no existe
-   **500 Internal Server Error**: Error del servidor

## Notas Importantes

1. **Seguridad**: Esta API es pública y no requiere autenticación
2. **Rate Limiting**: No implementado actualmente
3. **Cache**: Los datos son obtenidos directamente de la base de datos
4. **Conversación**: Incluye todos los mensajes ordenados cronológicamente
5. **SLA**: Cálculos automáticos basados en la categoría del ticket
6. **Fechas**: Formato ISO 8601 + versión legible para humanos

## Testing

Para probar la API puedes usar herramientas como:

-   **Postman**
-   **Insomnia**
-   **cURL** desde terminal
-   **Thunder Client** (VS Code)

### Ejemplo de prueba rápida:

```bash
curl -X GET "http://localhost/api/tickets/1" -H "Accept: application/json"
```
