# Centralizador Paccioli - Guía de Despliegue

## Requisitos
- XAMPP 8.0+ (Apache + MySQL/MariaDB)
- PHP 8.0+
- Git

---

## 📥 Clonar e Instalar en Nueva PC

### 1. Clonar repositorio
```bash
cd C:\xampp\htdocs
git clone https://github.com/TU_USUARIO/centralizador_paccioli2.git
cd centralizador_paccioli2
```

### 2. Iniciar servicios XAMPP
- Abrir **XAMPP Control Panel**
- Start: **Apache** ✓  **MySQL** ✓

### 3. Crear Base de Datos
**Opción A - phpMyAdmin (GUI):**
1. Abrir `http://localhost/phpmyadmin`
2. Importar → Seleccionar `schema.sql` → Continuar

**Opción B - Línea de comandos:**
```bash
mysql -u root -p < schema.sql
# Password: (enter - vacío en XAMPP default)
```

### 4. Configurar Conexión (si cambiaste credenciales MySQL)
Editar `config/db.php`:
```php
$user = 'root';
$pass = 'tu_password_si_cambiaste';  // default: ''
```

### 5. Migrar Datos Iniciales (OBLIGATORIO primera vez)
```bash
php migrate_json_to_db.php
```
> Crea usuario docente + migra estudiantes/notas de JSON a MySQL

### 6. Verificar Funcionamiento
Abrir: `http://localhost/centralizador_paccioli2`

**Credenciales por defecto:**
- **Docente**: `paccioli2026`
- **Estudiantes**: Los creados en "Gestionar Estudiantes"

### 7. Probar Flujo Completo
1. Login docente → Dashboard → Registrar nota
2. Gestionar Estudiantes → Crear estudiante "Juan Test" pass "1234"
3. Logout → Login estudiante "Juan Test" / "1234" → Ver sus notas
4. Verificar en phpMyAdmin: tablas `estudiantes`, `notas`, `docentes` con datos

---

## 🔧 Configuración Adicional Producción

### VirtualHost Apache (Opcional - URL limpia)
`C:\xampp\apache\conf\extra\httpd-vhosts.conf`:
```apache
<VirtualHost *:80>
    DocumentRoot "C:/xampp/htdocs/centralizador_paccioli2"
    ServerName paccioli.local
    <Directory "C:/xampp/htdocs/centralizador_paccioli2">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```
`C:\Windows\System32\drivers\etc\hosts`:
```
127.0.0.1  paccioli.local
```
Reiniciar Apache → Acceder: `http://paccioli.local`

### HTTPS Local (mkcert)
```bash
mkcert -install
mkcert paccioli.local
# Configurar SSL en httpd-ssl.conf
```

### Backup Automático BD
```bash
# Agregar a Task Scheduler (Windows) o cron (Linux)
mysqldump -u root centralizador_paccioli > backup_$(date +%F).sql
```

---

## 📁 Estructura del Proyecto
```
centralizador_paccioli2/
├── config/
│   └── db.php              # Conexión PDO
├── models/
│   ├── Database.php
│   ├── Carrera.php
│   ├── Estudiante.php
│   └── Nota.php
├── view/
│   ├── cabecera.php
│   ├── cabecera_estudiante.php
│   ├── pie.php
│   └── img/
├── index.php               # Login (docente + estudiante)
├── dashboard.php           # Panel docente (CRUD notas)
├── dashboard_estudiante.php# Panel estudiante (solo lectura)
├── gestion_estudiantes.php # CRUD cuentas estudiantes
├── logout.php
├── schema.sql              # Esquema BD completo
├── migrate_json_to_db.php  # Migración one-time JSON→MySQL
└── README.md
```

---

## 🐛 Troubleshooting

| Problema | Solución |
|----------|----------|
| "Connection refused" | Verificar MySQL corriendo en XAMPP |
| "Access denied" | Revisar `config/db.php` user/pass |
| "Table doesn't exist" | Ejecutar `schema.sql` de nuevo |
| Login docente falla | Ejecutar `php migrate_json_to_db.php` |
| Error 500 | Verificar `php_error.log` en XAMPP |
| Caracteres extraños (Contadur�a) | Ejecutar `php fix_carreras.php` si existe, o re-importar schema.sql con codificación UTF-8 |

---

## 🔐 Seguridad Producción
- Cambiar password docente en BD: `UPDATE docentes SET password = '$2y$10$...'`
- Usar `.env` para credenciales (no commitear)
- Habilitar `PDO::ATTR_EMULATE_PREPARES => false` (ya configurado)
- Validar/sanitizar todas las entradas (implementado en modelos)

---

## 📝 Notas Importantes

### Base de Datos
- **docentes**: 1 registro con password hash de `paccioli2026`
- **carreras**: 6 carreras predefinidas
- **estudiantes**: Creados por docente desde panel
- **notas**: 4 notas + semestre + FK a estudiante y carrera

### Migración de Datos Antiguos
Si vienes de versión anterior con archivos JSON:
1. Ejecutar `schema.sql` (crea tablas vacías)
2. Ejecutar `php migrate_json_to_db.php` (migra datos)
3. Verificar en phpMyAdmin

### Cambiar Password Docente
```sql
UPDATE docentes SET password = '$2y$10$NUEVO_HASH' WHERE id = 1;
```
Generar hash: `php -r "echo password_hash('nueva_pass', PASSWORD_DEFAULT);"`

---

## 🚀 Deploy a Servidor Linux (Producción)

1. **Clonar**: `git clone <url> /var/www/html/centralizador_paccioli2`
2. **BD**: `mysql -u root -p < schema.sql`
3. **Config**: Editar `config/db.php` con credenciales servidor
4. **Permisos**: `chown -R www-data:www-data /var/www/html/centralizador_paccioli2`
5. **Apache/Nginx**: VirtualHost apuntando a `/var/www/html/centralizador_paccioli2`
6. **SSL**: Certbot Let's Encrypt
7. **Cron backup**: `0 2 * * * mysqldump -u user -ppass centralizador_paccioli > /backups/db_$(date +\%F).sql`