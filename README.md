### **Estructura Web Funcional Detallada**

**Aquí integramos tus requerimientos en una estructura clara y organizada.**

#### ÁREA PÚBLICA (Frontend: HTML + JS)

**1. Página de Inicio (**index.html**)**

* **Objetivo:** **Captar al visitante y guiarlo a la compra o exploración.**
* **Componentes y Diseño:**

  * **Header:** **Logo, menú de navegación, y el** **Selector de Idioma (ES / EN)** **bien visible.**
  * **Hero Section:** **Video/imagen impactante de Raqchi. Encima, un titular y el botón principal:** **“Compra tu ticket en 3 clics”**.
  * **Sección de Filtro Inteligente:** **Un bloque interactivo con filtros:**

    * **¿Qué tipo de visitante eres?** **(Nacional, Extranjero, Estudiante).**
    * **¿Buscas un guía?** **(Sí / No).**
    * **Duración estimada** **(desplegable).**
    * **Presupuesto** **(slider de precio opcional).**
    * **Al aplicar, muestra tarjetas con combos sugeridos ("Turista Extranjero + Guía Bilingüe").**
  * **Sección de Testimonios:** **Un carrusel con 3-4 tarjetas de reseñas destacadas (foto, nombre, calificación, extracto del comentario). Botón "Ver todas las experiencias".**
  * **Sección de Servicios:** **Iconos o tarjetas pequeñas que enlazan al catálogo (Guías, Gastronomía, Talleres).**
* **Lógica y Tecnología:**

  * **JS:** **Gestiona la interactividad del filtro y el carrusel de testimonios.**
  * **PHP/Supabase:** **Los testimonios se cargan dinámicamente desde la tabla** **reseñas** **en Supabase a través de un script PHP.**

**2. Página de Compra de Tickets (**compra.html**)**

* **Objetivo:** **Proceso de compra rápido, claro y seguro.**
* **Componentes y Diseño (Flujo en 3 Pasos):**

  * **Paso 1: Selección de Ticket y Servicios.**

    * **Calendario para elegir fecha.**
    * **Selector de tipo de ticket (Adulto Nacional, etc.).**
    * **Desplegable clave: "¿Incluir guía?" (Solo Entrada / Entrada + Guía).**
    * **Si se selecciona "Entrada + Guía", se muestra la disponibilidad horaria y opcionalmente las fichas de los guías disponibles para ese día.**
    * **El precio total se actualiza en tiempo real.**
  * **Paso 2: Datos del Comprador.**

    * **Formulario simple: Nombre, Apellido, Email, Documento de Identidad.**
  * **Paso 3: Pago y Confirmación.**

    * **Logos de métodos de pago visibles:** **Visa/Mastercard, Yape, Plin, PayPal.**
    * **Un iframe o redirección a la pasarela de pago.**
    * **Checkbox obligatorio: "Acepto los Términos y la Política de Cancelación" (con enlace).**
* **Lógica y Tecnología:**

  * **JS:** **Valida el formulario, actualiza el precio dinámicamente.**
  * **PHP:**

    * **Recibe los datos del formulario JS.**
    * **Se conecta a la pasarela de pago para procesar la transacción.**
    * **Si el pago es exitoso, llama a Supabase para:**

      * **Guardar la venta en la tabla** **tickets**.
      * **Generar un PDF con el QR (usando una librería como FPDF).**
      * **Subir el PDF a** **Supabase Storage**.
      * **Enviar el email de confirmación con el PDF adjunto.**

**3. Catálogo de Servicios (**servicios.html**)**

* **Objetivo:** **Mostrar la oferta complementaria de Raqchi.**
* **Componentes y Diseño:**

  * **Sección de Guías Turísticos:**

    * **Grid de tarjetas, cada una con:** **foto del guía, nombre, idiomas que habla, y una breve bio.**
    * **Botón "Ver perfil completo" que abre un modal con más detalles y sus reseñas.**
  * **Sección de Servicios del Complejo:**

    * **Listado claro con iconos: Baños, Estacionamiento, Cafetería, Seguridad, Horarios.**
* **Lógica y Tecnología:**

  * **JS/PHP/Supabase:** **Los perfiles de los guías y los servicios se cargan desde las tablas** **guias** **y** **servicios_generales** **en Supabase.**

**4. Comentarios y Experiencias (**reseñas.html**)**

* **Objetivo:** **Generar confianza a través de opiniones reales y validadas.**
* **Componentes y Diseño:**

  * **Formulario de envío (solo visible para quienes visitaron):**

    * **El enlace para dejar la reseña se enviaría en un correo post-visita. El enlace contendría un token único asociado al ticket para validarlo.**
    * **Campos: Calificación (estrellas), Título, Comentario, Subir foto (opcional).**
  * **Visualización de Reseñas:**

    * **Filtros inteligentes:** **Por estrellas, idioma.**
    * **Buscador por palabra clave.**
    * **Elemento visual destacado: Nube de palabras (Word Cloud)** **generada con los términos más frecuentes ("guía", "historia", "increíble", "baños").**
* **Lógica y Tecnología:**

  * **JS/PHP/Supabase:** **El formulario envía la reseña a un script PHP que la guarda en la tabla** **reseñas** **en Supabase con estado "pendiente". El admin la aprueba desde su dashboard. La nube de palabras se puede generar con una librería JS (como** **d3-cloud**) a partir de los datos de las reseñas.

**5. Páginas Legales y de Ayuda**

* **Libro de Reclamaciones Digital (**libro-reclamaciones.html**):**

  * **Formulario que cumple con la normativa de INDECOPI.**
  * **Lógica:** **Al enviarse, un script PHP guarda el reclamo en la tabla** **reclamos** **de Supabase y envía un correo de confirmación automático al usuario con su número de caso.**
* **Centro de Ayuda (**ayuda.html**):**

  * **Sección de** **Preguntas Frecuentes (FAQ)** **con desplegables.**
  * **Enlace para descargar el** **Manual de Usuario en PDF**.
  * **Formulario de contacto técnico.**
  * **Botón de WhatsApp flotante** **para contacto directo.**
* **Páginas de Políticas (**politicas-privacidad.html**,** **terminos-condiciones.html**):

  * **Contenido estático, accesible desde el pie de página de todo el sitio.**

---

#### ÁREA INTERNA (Acceso con Login)

**1. Panel del Administrador General (**/admin**)**

* **Acceso:** **admin.html** **(protegido por login).**
* **Interfaz:** **Dashboard moderno con menú lateral.**
* **Módulos:**

  * **Dashboard:** **Gráficos (ventas, visitantes por tipo, guías más solicitados, palabras clave de reseñas).**
  * **Gestión de Ventas:** **Ver, filtrar y exportar todas las ventas.**
  * **Gestión de Servicios:** **Crear/editar perfiles de guías, precios, horarios.**
  * **Gestión de Contenidos:** **Editor simple para textos e imágenes de las páginas públicas.**
  * **Reseñas y Comentarios:** **Panel para** **moderar** **(aprobar/rechazar) nuevas reseñas.**
  * **Libro de Reclamaciones:** **Bandeja de entrada con los reclamos, para asignarles estado (pendiente, resuelto) y responder.**
  * **Gestión de Usuarios:** **Crear cuentas para "Vendedores de Taquilla".**
* **Lógica y Tecnología:**

  * **JS/Supabase:** **El login usa** **Supabase Auth**. Una vez logueado, JS realiza llamadas a scripts PHP seguros que consultan Supabase. **RLS en Supabase** **garantiza que solo el rol "admin" pueda acceder a toda la información. Los gráficos se crean con** **Chart.js**.

**2. Panel del Vendedor de Tickets (Taquilla) (**/taquilla**)**

* **Acceso:** **taquilla.html** **(protegido por login con rol "vendedor").**
* **Interfaz:** **Simple, rápida, optimizada para tablets y modo oscuro opcional.**
* **Módulos:**

  * **Nueva Venta:** **Formulario ultra-simplificado para venta en persona.**
  * **Historial del Día:** **Listado de ventas realizadas en su turno.**
  * **Modo Offline:**

    * **Componente clave:** **Un indicador visual (ej. un punto verde/rojo) muestra el estado de la conexión.**
    * **Lógica:**

      * **Si hay internet, la venta se guarda directamente en Supabase vía PHP.**
      * **Si no hay internet,** **JavaScript guarda la venta en IndexedDB (base de datos del navegador)**. Se genera un ticket y QR localmente.
      * **Un** **Service Worker** **se ejecuta en segundo plano. Cuando detecta que la conexión ha vuelto, toma las ventas de IndexedDB y las envía en lote al servidor (PHP/Supabase) para sincronizar.**
* **Lógica y Tecnología:**

  * **Supabase RLS** **es fundamental aquí. La política de seguridad solo permitirá a este rol crear ventas y ver las que él mismo ha creado (**WHERE user_id = auth.uid()**). No podrá ver datos de otros vendedores ni acceder a otros módulos.**

---

### Resumen del Flujo de Datos y Seguridad

* **Visitante (Frontend JS)** **-> Pide datos a ->** **Servidor (Script PHP)**.
* **Servidor (Script PHP)** **-> Usa su clave secreta para hablar con ->** **Supabase**.
* **Supabase** **-> Devuelve los datos al ->** **Servidor (Script PHP)**.
* **Servidor (Script PHP)** **-> Envía los datos finales al ->** **Visitante (Frontend JS)**.

**Este modelo es simple, seguro y cumple con tus requisitos tecnológicos, creando una base sólida y escalable para el portal digital de Raqchi.**
