## Contexto base

Vincebus's FTP es una aplicacion web construida en Laravel 13 con InertiaJS y Vue 3 el cual imita a 'google drive' es decir, un usuario tiene una cuenta el cual puede usarla para almacenar datos. 

*El administrador de momento será la unica persona que gestionara los planes de usuario, el plan consiste basicamente en el limite de almacenamiento y podrá configurarlo desde la interfaz grafica del usuario. El administrador no tiene limites.

*La idea de esta aplicación surge con la intencion de reemplazar la nube debido para prevenir la perdida de archivos personales en caso de algun acontecimiento mundial los servidores de google se caigan.

*La interfaz gráfica debe ser sencilla, construida con TailwindCSS, un estilo sobrio y simple.

*Este sistema proyecto será desplegado usando docker, debe crearse un docker-compose para 'dev' y otro para 'prod' (producción)

*Se utilizará laravel-permission de spatie para gestionar permisos y roles. La plataforma limitara acceso a ciertas funcionalidades dependiendo del perfil de usuario utilizando permisos definidos.

El sistema será desplegado en un equipo con Debian Trixie, 16 GB de RAM y un procesador 771 Xeon L5420 de 4 Nucleos y bajo consumo. El administrador puede conectarse desde SSH a la maquina para hacer configuraciones de infraestructura.

El entorno de desarrollo no tiene PHP instalado, ya que es WSL. Creo que los primeros pasos seria crear el contenedor del proyecto con PHP/Composer para asi construirlo desde ahi

Respecto al front end: 

- Se utilizara el patron de composables, store con Pinia si es necesario
- Se utilizara la sintaxis de Composition API Script setup
- La estructura de archivos es la siguiente:
    -frontend
        - module
            - store
            - composables
            - components
            - views

## Analisis segun Gemini

El proyecto Vincebus's FTP es una excelente iniciativa de soberanía digital. Aquí tienes mi análisis técnico y opinión sobre la propuesta:

1. Stack Tecnológico (Laravel 13 + Inertia + Vue 3)
Acierto Total: Es una combinación sumamente productiva. InertiaJS es perfecto para esto porque te permite construir un SPA (Single Page Application) con la velocidad de desarrollo de un monolito clásico de Laravel. Para una interfaz tipo "Drive" donde necesitas arrastrar archivos, modales rápidos y actualizaciones de estado en tiempo real, Vue 3 es la herramienta ideal.
TailwindCSS: Mantener la interfaz "sobria y simple" es clave. Tailwind te permitirá iterar rápido sin llenarte de archivos CSS personalizados.
2. Hardware e Infraestructura (El "Reto")
CPU Xeon L5420 (LGA771): Es un procesador legendario, pero hay que ser realistas: es tecnología de 2008. Aunque tiene 4 núcleos, carece de instrucciones modernas de aceleración (como AVX).
Impacto: Para servir archivos (I/O) funcionará bien, pero si implementas generación de miniaturas (thumbnails) de fotos o transcodificación de video (streaming desde el navegador), el CPU sufrirá.
Debian Trixie (Testing/Unstable): Usar la versión Testing de Debian en producción es arriesgado para un servidor de archivos. Te sugeriría Debian 12 (Bookworm) por estabilidad, a menos que necesites específicamente un kernel o drivers muy nuevos que solo Trixie ofrezca.
Docker: Es el camino correcto. Facilita enormemente las actualizaciones de la base de datos y de PHP sin ensuciar el sistema operativo host.
3. Observaciones Críticas para el Éxito
Gestión de Archivos Grandes: Laravel y PHP por defecto no manejan bien archivos de gigabytes. Tendrás que configurar client_max_body_size en Nginx y post_max_size en PHP. Además, para una experiencia "Premium", te recomiendo implementar Subidas por partes (Chunked Uploads) en el frontend para que no fallen si hay un micro-corte de red.
Prevención de Pérdida (La Motivación): Si el objetivo es que no se pierdan archivos si Google cae, el hardware es tu punto débil único. Un solo disco fallando en ese Xeon y pierdes todo. ¿Has considerado implementar un RAID 1 (espejo) o un backup automatizado a un disco externo?
Seguridad: Al ser una "nube personal", la superficie de ataque es alta. Usar spatie/laravel-permission es excelente para la lógica interna, pero asegúrate de implementar HTTPS obligatorio (Let's Encrypt) y considera usar Fail2Ban en el equipo Debian para proteger el acceso SSH.
Resumen
La idea es muy sólida y necesaria. La arquitectura de software está perfectamente elegida. Mi única preocupación es la edad del hardware y la estrategia de respaldo físico.

## Update 21.03.2026

Actualemtne el proyecto esta montado en el servidor y desplegado publicamente. Se hizo
modifciaciones a nivel de configuracion de nginx y el contendor de produccion para usar
HTTPS en vez de HTTP, funcionando correctamente.

Tambiien se implemento un makefile para poder desplegar localemnte en un entorno de desarrollo o en produccion.

TODO:
- Hace falta implementar un metodo de registro mas seguro mediante verificacion. Se me
ocurre lo siguiente: Durante el proceso de registro, luego que el usuario haya completado todos los datos requeridos. Una vez intentando iniciar sesion por primera vez se le solicitara un codigo de verificacion que le llegara al email, una vez ingresado correctamente el usuario podra utilizar la plataforma.
