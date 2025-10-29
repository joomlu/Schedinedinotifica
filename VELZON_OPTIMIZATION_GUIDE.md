# Guía de Optimización Velzon - Sistema Schedine di Notifica

## 📚 Índice
1. [Componentes creados](#componentes-creados)
2. [Cómo usar los componentes](#cómo-usar-los-componentes)
3. [Layouts disponibles](#layouts-disponibles)
4. [Migración de vistas existentes](#migración-de-vistas-existentes)
5. [Configuración del tema](#configuración-del-tema)

---

## 🧩 Componentes Creados

### 1. Card Component (`<x-card>`)
**Ubicación:** `resources/views/components/card.blade.php`

**Uso básico:**
```blade
<x-card title="Título de la tarjeta">
    Contenido aquí
</x-card>
```

**Con acciones en el header:**
```blade
<x-card title="Gruppi">
    <x-slot name="headerActions">
        <button class="btn btn-primary">Nuevo</button>
    </x-slot>
    
    Contenido
</x-card>
```

**Props disponibles:**
- `title`: Título de la card
- `subtitle`: Subtítulo opcional
- `headerActions`: Slot para botones en el header
- `noPadding`: Si true, remueve padding del body

---

### 2. Modal Component (`<x-modal>`)
**Ubicación:** `resources/views/components/modal.blade.php`

**Uso:**
```blade
<x-modal id="createModal" title="Nuovo Gruppo">
    <form>
        <!-- Campos del formulario -->
    </form>
    
    <x-slot name="footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Chiudi</button>
        <button class="btn btn-primary">Salva</button>
    </x-slot>
</x-modal>
```

**Props:**
- `id`: ID único del modal
- `title`: Título del modal
- `size`: sm, md, lg, xl
- `centered`: Centrar verticalmente
- `scrollable`: Permitir scroll interno

---

### 3. Form Input Component (`<x-form.input>`)
**Ubicación:** `resources/views/components/form/input.blade.php`

**Uso:**
```blade
<x-form.input 
    name="name" 
    label="Nome" 
    type="text"
    placeholder="Inserisci nome..."
    required />
```

**Props:**
- `name`: Nombre del campo (required)
- `label`: Etiqueta del campo
- `type`: Tipo de input (text, email, password, date, etc.)
- `value`: Valor inicial
- `required`: Campo obligatorio
- `placeholder`: Texto placeholder
- `helpText`: Texto de ayuda

---

### 4. Form Select Component (`<x-form.select>`)
**Ubicación:** `resources/views/components/form/select.blade.php`

**Uso:**
```blade
<x-form.select 
    name="group_id" 
    label="Gruppo"
    :options="['1' => 'Gruppo A', '2' => 'Gruppo B']"
    required />
```

O con array de objetos:
```blade
<x-form.select 
    name="group_id" 
    label="Gruppo"
    :options="$groups->pluck('name', 'id')"
    required />
```

---

### 5. Button Component (`<x-button>`)
**Ubicación:** `resources/views/components/button.blade.php`

**Uso:**
```blade
<x-button 
    href="/customers" 
    variant="primary" 
    icon="ri-add-circle-line">
    Nuovo Cliente
</x-button>
```

**Props:**
- `href`: URL del enlace
- `variant`: primary, secondary, success, danger, warning, info
- `size`: sm, lg
- `icon`: Clase del icono (Remix Icons)
- `outline`: true para botón outline

---

### 6. DataTable Component (`<x-datatable>`)
**Ubicación:** `resources/views/components/datatable.blade.php`

**Uso:**
```blade
<x-datatable 
    id="customersTable" 
    :columns="['ID', 'Nome', 'Email', 'Azioni']">
    @foreach($customers as $customer)
    <tr>
        <td>{{ $customer->id }}</td>
        <td>{{ $customer->name }}</td>
        <td>{{ $customer->email }}</td>
        <td>
            <a href="#" class="link-success"><i class="ri-edit-2-line"></i></a>
        </td>
    </tr>
    @endforeach
</x-datatable>
```

---

## 🎨 Layouts Disponibles

### 1. Vertical (Default)
**Archivo:** `resources/views/layouts/master.blade.php`
- Sidebar izquierdo
- Topbar fijo
- **Atributos en `<html>`:** `data-layout="vertical"`

### 2. Horizontal
**Archivo:** `resources/views/layouts/layouts-horizontal.blade.php`
- Menú horizontal top
- Sin sidebar lateral

### 3. Two Column
**Archivo:** `resources/views/layouts/layouts-two-column.blade.php`
- Sidebar compacto con iconos
- Panel secundario con texto

### 4. Detached
**Archivo:** `resources/views/layouts/layouts-detached.blade.php`
- Layout con contenedor central
- Sidebar detached

---

## 🔄 Migración de Vistas Existentes

### Ejemplo: Convertir group/list.blade.php

**❌ ANTES (código antiguo):**
```blade
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Groups</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="datatable" class="table">
                        <!-- ... -->
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
```

**✅ DESPUÉS (con componentes Velzon):**
```blade
<div class="row">
    <div class="col-lg-12">
        <x-card :title="__('translation.Group')">
            <x-slot name="headerActions">
                <x-button 
                    variant="primary" 
                    icon="ri-add-circle-line"
                    data-bs-toggle="modal" 
                    data-bs-target="#createModal">
                    @lang('translation.new')
                </x-button>
            </x-slot>

            <x-datatable 
                id="groupsTable" 
                :columns="['ID', __('translation.Name'), __('translation.actions')]">
                @foreach($groups as $group)
                <tr>
                    <td>{{ $group->id }}</td>
                    <td>{{ $group->name }}</td>
                    <td>...</td>
                </tr>
                @endforeach
            </x-datatable>
        </x-card>
    </div>
</div>
```

---

## ⚙️ Configuración del Tema

### Cambiar el layout por defecto

Edita `config/app.php` o crea una configuración personalizada:

```php
// config/velzon.php
return [
    'layout' => env('VELZON_LAYOUT', 'vertical'), // vertical, horizontal, twocolumn, detached
    'topbar' => env('VELZON_TOPBAR', 'light'), // light, dark
    'sidebar' => env('VELZON_SIDEBAR', 'light'), // light, dark, gradient
    'sidebar_size' => env('VELZON_SIDEBAR_SIZE', 'lg'), // sm, lg, md, sm-hover
    'sidebar_image' => env('VELZON_SIDEBAR_IMAGE', 'none'), // none, img-1, img-2, img-3, img-4
];
```

### Permitir al usuario seleccionar el tema

1. **Agregar campo a la tabla users:**
```php
Schema::table('users', function (Blueprint $table) {
    $table->string('preferred_layout')->default('vertical')->after('avatar');
});
```

2. **Modificar master.blade.php:**
```blade
<html 
    lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
    data-layout="{{ auth()->user()->preferred_layout ?? 'vertical' }}" 
    data-topbar="light" 
    data-sidebar="light">
```

3. **Crear página de configuración del tema:**
```blade
<!-- resources/views/settings/theme.blade.php -->
@extends('layouts.master')

@section('content')
<x-card title="Configurazione Tema">
    <form method="POST" action="{{ route('settings.theme.update') }}">
        @csrf
        
        <x-form.select 
            name="preferred_layout" 
            label="Layout"
            :options="[
                'vertical' => 'Verticale (Sidebar)',
                'horizontal' => 'Orizzontale (Top Menu)',
                'twocolumn' => 'Due Colonne',
                'detached' => 'Detached'
            ]"
            :value="auth()->user()->preferred_layout" />
        
        <button type="submit" class="btn btn-primary">Salva Preferenze</button>
    </form>
</x-card>
@endsection
```

---

## 📋 Checklist de Migración

### Vistas a actualizar (en orden de prioridad):

- [ ] ✅ `group/list.blade.php` → Ya optimizada como ejemplo
- [ ] `subgroup/list.blade.php`
- [ ] `subgroup1/list.blade.php`
- [ ] `title/list.blade.php`
- [ ] `typedoc/list.blade.php`
- [ ] `released/list.blade.php`
- [ ] `typestreet/list.blade.php`
- [ ] `customers/list.blade.php`
- [ ] `customers/new.blade.php`
- [ ] `customers/edit.blade.php`
- [ ] `componenti/list.blade.php`
- [ ] `componenti/new.blade.php`
- [ ] `componenti/edit.blade.php`
- [ ] `schedina/list.blade.php`
- [ ] `schedina/new.blade.php`
- [ ] `schedina/edit.blade.php`
- [ ] `estructura/index.blade.php`
- [ ] `arrivals/list.blade.php`

---

## 🎯 Pasos para Aplicar a Todo el Sistema

### 1. Actualizar vistas una por una
Usa el archivo `list-optimized.blade.php` como referencia y aplica el mismo patrón.

### 2. Mantener consistencia
- **Siempre** usa componentes Velzon
- **No mezcles** HTML custom con componentes
- **Traduce** todos los textos usando `@lang('translation.key')`

### 3. Probar cada vista
Después de actualizar cada vista:
```bash
php artisan view:clear
```

### 4. Configurar tema por defecto
Edita `resources/views/layouts/master.blade.php` línea 2:
```blade
<html lang="it" 
    data-layout="vertical"           <!-- vertical, horizontal, twocolumn -->
    data-topbar="light"               <!-- light, dark -->
    data-sidebar="light"              <!-- light, dark, gradient -->
    data-sidebar-size="lg"            <!-- lg, md, sm, sm-hover -->
    data-sidebar-image="none">        <!-- none, img-1, img-2, img-3, img-4 -->
```

---

## 🚀 Siguiente Paso Recomendado

**Opción A:** Aplicar componentes manualmente vista por vista
**Opción B:** Crear un comando Artisan que automatice la conversión

¿Quieres que te ayude a:
1. Convertir las vistas restantes una por una?
2. Crear un sistema de temas dinámico con preferencias de usuario?
3. Ambas opciones?

Dime qué prefieres y continuamos optimizando el sistema completo.
