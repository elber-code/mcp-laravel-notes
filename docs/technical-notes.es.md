# Notas Técnicas y Solución de Problemas (Troubleshooting)

Este documento contiene notas técnicas sobre decisiones de arquitectura, peculiaridades de los frameworks utilizados (Laravel, Livewire, Alpine.js) y soluciones a problemas comunes encontrados durante el desarrollo.

## 1. Livewire 3: Eventos en `<x-slot>` (Headers y Layouts)

### El Problema
Al colocar botones o elementos interactivos dentro de un `<x-slot name="header">` (común en Jetstream y plantillas de Laravel) e intentar usar eventos nativos de Livewire como `wire:click="$dispatch('evento')"`, el evento **no se dispara** y Livewire lo ignora.

### La Causa
Al no ser parte del slot principal (`$slot`) del layout, sino de un slot aislado (`$header`), el bloque se renderiza en la plantilla principal (`layouts/app.blade.php`) fuera del contexto del componente de Livewire. Al quedar fuera, los atributos `wire:` simplemente no funcionan.

### La Solución
Como los `wire:` no funcionan ahí, se ocupan obligatoriamente los eventos de Alpine (`x-on:click`). Sin embargo, para que Alpine.js pueda escuchar y despachar estos eventos en un bloque aislado, **es estrictamente necesario levantarlo inicializando un contexto con `x-data`**.

1. **Levantar el contexto con `x-data`**: Asegúrate de que el contenedor del botón tenga el atributo `x-data` para inicializar Alpine.
2. **Disparar el evento con Alpine**: Usa `x-on:click` y `$dispatch` para emitir un evento global en el navegador.
3. **Evitar recargas del navegador**: Si usas componentes como `<x-button>` de Jetstream, estos renderizan `<button type="submit">` por defecto. Debes forzar `type="button"` para que no recargue la página.

**Ejemplo Correcto:**
```html
<x-slot name="header">
    <div class="flex justify-between items-center" x-data>
        <h2>Mis Notas</h2>
        <x-button type="button" x-on:click="$dispatch('open-create-modal')">
            Crear Nota
        </x-button>
    </div>
</x-slot>
```

Y en el componente Livewire, se escucha este evento global del navegador utilizando el atributo `#[On]`:

```php
use Livewire\Attributes\On;

#[On('open-create-modal')]
public function openModal()
{
    $this->isOpen = true;
}
```

## 2. Botones de Acción y Recargas Inesperadas (Page Reloads)

### El Problema
Al intentar abrir un modal con un botón (ya sea nativo o `<x-button>`), la página se recarga instantáneamente, haciendo que el modal se cierre antes de siquiera ser visible.

### La Causa
Los navegadores otorgan el comportamiento de `submit` por defecto a los botones (`<button>`) cuando no tienen un tipo especificado, especialmente si creen que forman parte de un formulario (o en el caso de los componentes prefabricados que ya lo traen hardcodeado).

### La Solución
Siempre añade explícitamente `type="button"` a cualquier botón cuya única función sea ejecutar JavaScript, interactuar con Alpine.js o desencadenar un evento de Livewire:

```html
<!-- Incorrecto: Puede causar recarga -->
<button x-on:click="abrir()">Abrir</button>

<!-- Correcto -->
<button type="button" x-on:click="abrir()">Abrir</button>
```


## 3. Sistema de Etiquetas (Tags) y Arrays JSON

### Almacenamiento de Datos
Las etiquetas se almacenan como un array JSON en la columna `tags` de las tablas `notes` y `key_notes`. En los modelos Eloquent, se castean a `array`:
```php
protected $casts = [
    'tags' => 'array',
];
```

### Consistencia y Sincronización
Se utiliza una tabla separada `tags` para almacenar un catálogo global de etiquetas por usuario. Esto garantiza que:
1. Los usuarios puedan ver una lista de todas sus etiquetas para filtrar.
2. El asistente de IA pueda consultar las etiquetas existentes via el recurso `tags://all`.
3. Las etiquetas se normalizan (minúsculas y sin espacios) antes de guardarse.

### Trait de Livewire
La lógica para agregar, eliminar y sugerir etiquetas está centralizada en `App\Livewire\Traits\HasTags.php`. Esto permite que tanto las notas estándar como las notas clave compartan exactamente el mismo comportamiento de UI y lógica de backend.

## 4. Arquitectura del Servidor MCP (Laravel MCP)

El proyecto implementa un servidor Model Context Protocol (MCP) utilizando el paquete `laravel/mcp`. Esto permite que agentes de IA interactúen con los datos de la aplicación.

### Recursos vs. Herramientas
- **Herramientas (Tools)**: Se utilizan para acciones que modifican el estado (ej. `create-note`, `edit-key-note`) o consultas complejas con parámetros.
- **Recursos (Resources)**: Se utilizan para proporcionar contexto de datos crudos mediante URIs.
    - Los recursos estáticos (como `tags://all`) permiten que la IA "vea" metadatos disponibles rápidamente.
    - Los recursos dinámicos (como `timeline://recent/{days}`) utilizan plantillas de URI para permitir que la IA recupere contextos específicos.

### Plantillas de Prompts
Los prompts son conjuntos de instrucciones predefinidas que pueden ser disparados por el usuario en el cliente de IA. Ayudan a estandarizar flujos de trabajo complejos, como el flujo de **"Summarize Timeline"**, que orquestra la lectura de registros recientes y la actualización de la memoria estructurada.

### Formato de Respuesta de Prompts (Mensajes con Rol)
El método `handle()` de un Prompt debe retornar un **array** de mensajes con rol, no un único `Response`. Se usa `->asAssistant()` para marcar el mensaje de sistema/contexto:

```php
public function handle(Request $request): array
{
    $days = $request->get('days', 7);
    return [
        Response::text("Eres un asistente experto. Resume los últimos {$days} días de notas.")->asAssistant(),
        Response::text("Por favor lee timeline://recent/{$days} y actualiza memory://core."),
    ];
}
```

`SummarizeTimelinePrompt` está implementado con este formato de array.

### Atributos `#[Name]` y `#[Title]`
El paquete distingue entre un identificador legible por máquina y una etiqueta legible por humanos:

```php
#[Name('get-memory')]      // ID de máquina que la IA usa para llamar al tool
#[Title('Obtener Memoria')] // etiqueta legible mostrada en la UI
#[Description('Obtiene...')] // descripción de lo que hace
class GetMemoryTool extends Tool {}
```

Actualmente los tools del proyecto solo definen `#[Description]`. Se recomienda agregar `#[Name]` y `#[Title]`.

### `#[MimeType]` en Recursos
Los recursos que retornan JSON deben declarar explícitamente su tipo MIME:

```php
#[Uri('memory://core')]
#[MimeType('application/json')]
class MemoryResource extends Resource {}
```

Actualmente `MemoryResource`, `TagsResource` y `RecentTimelineResource` no tienen este atributo.

### Anotaciones de Herramientas (Tool Annotations)
Las anotaciones semánticas ayudan a los clientes de IA a entender el comportamiento de cada tool:

```php
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[IsReadOnly]    // no modifica el estado
#[IsIdempotent]  // llamadas repetidas no tienen efecto adicional
#[IsDestructive] // puede eliminar o sobreescribir datos
class GetMemoryTool extends Tool {}
```

Los tools de solo lectura del proyecto (`get-memory`, `get-recent-notes`, `get-month-notes`, `get-last-key-notes`, `get-all-tags`) deberían tener `#[IsReadOnly]` y `#[IsIdempotent]`.

### Anotaciones de Recursos (Resource Annotations)
Los recursos pueden llevar metadatos de audiencia y prioridad:

```php
use Laravel\Mcp\Enums\Role;
use Laravel\Mcp\Server\Annotations\Audience;
use Laravel\Mcp\Server\Annotations\Priority;

#[Audience(Role::Assistant)] // este recurso está pensado para ser consumido por la IA
#[Priority(0.9)]             // puntuación de relevancia de 0.0 a 1.0
class MemoryResource extends Resource {}
```

### JSON en Tools: `Response::json()` vs `Response::structured()`
Todos los tools usan `Response::json()`, un wrapper compacto sobre `json_encode` que retorna un `Response`:

```php
return Response::json([
    'key'        => $note->key,
    'updated_at' => $note->updated_at->toISOString(),
]);
```

`Response::structured()` existe como alternativa y adjunta los datos como campo `structuredContent` del protocolo MCP, pero retorna `ResponseFactory` (no `Response`), lo que obligaría a cambiar la firma de retorno de todos los tools. `Response::json()` mantiene el tipo consistente y produce JSON compacto sin `JSON_PRETTY_PRINT` — menos tokens para que la IA procese.

**Principios de diseño de respuesta aplicados:**
- Los tools de creación/edición retornan solo una confirmación (id o key + timestamp), no el contenido que la IA acaba de enviar.
- Los tools de lectura retornan todos los campos que la IA necesita para procesar o referenciar datos después.
- `id` se mantiene solo en notas de timeline (necesario para llamar a `edit-note`). Las key notes omiten `id` ya que la IA las referencia por `key` string.
- Los campos meta tipo `count`/`total_found` se unificaron como `total` para consistencia.

### `outputSchema()` en Tools
Los tools pueden declarar opcionalmente la forma de su salida además del input schema:

```php
public function outputSchema(JsonSchema $schema): array
{
    return [
        'id'         => $schema->integer()->required(),
        'title'      => $schema->string()->required(),
        'content'    => $schema->string()->required(),
        'created_at' => $schema->string()->required(),
    ];
}
```

### Registro Condicional (`shouldRegister`)
Cualquier Tool, Resource o Prompt puede ocultarse en tiempo de ejecución según el contexto del request:

```php
public function shouldRegister(Request $request): bool
{
    return $request?->user()?->hasVerifiedEmail() ?? false;
}
```

### Inyección de Dependencias en Tools
El service container de Laravel está disponible tanto en el constructor como en el método `handle`:

```php
public function handle(Request $request, MyService $service): Response
{
    return Response::structured($service->getData($request->user()));
}
```

### Testing
El paquete provee un helper de testing fluido:

```php
$response = $this->tool(GetMemoryTool::class)->handle([]);
$response = $this->tool(CreateNoteTool::class)->handle(['content' => 'Test']);
```

### Seguridad
El servidor MCP está expuesto en `/api/mcp/notes` y está protegido estrictamente por el middleware `auth:sanctum`. Cada solicitud debe incluir un token Bearer válido, lo que automáticamente limita todas las consultas a los datos del usuario autenticado.

