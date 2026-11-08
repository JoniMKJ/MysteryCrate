# 🎁 MysteryBox

Plugin de cajas misteriosas para **PocketMine-MP (API 5)** con **gestión de recompensas mediante forms** — sin GUI de cofres, sin dependencias externas.

**Autor:** joni

---

## ✨ Características

- Ítem de caja misteriosa personalizado (nether star, incolocable, muestra los drops posibles en su lore).
- Click derecho para abrir: consume una caja, lanza recompensas comunes + exclusivas y tira al suelo lo que no quepa en tu inventario.
- Cooldown configurable por jugador.
- **Gestión del loot completamente por forms** (`/mysterybox loot`):
  - Agregar el stack exacto que tienes en la mano (`+ Agregar item en mano`)
  - Quitar una recompensa específica de la lista
  - Vaciar un pool completo (con diálogo de confirmación)
- Cantidad de recompensas, mensajes y nombre de la caja configurables (`config.yml`).
- El loot se guarda serializado (base64 + NBT) dentro de `config.yml`.
- Cero dependencias externas.

## 📦 Instalación

1. Descarga o clona este repositorio.
2. Coloca la carpeta del plugin (o un build .phar/.zip) dentro del directorio `plugins/` de tu servidor.
3. Inicia el servidor una vez para generar `config.yml`.
4. En el juego, ejecuta `/mysterybox loot` y agrega items desde tu mano a los pools **Common** y **Exclusive**.
5. Reparte cajas con `/mysterybox give <jugador>` o `/mysterybox giveall`.

## 🛠️ Compilación

Para desarrollo, PMMP-MP puede cargar la carpeta del plugin directamente. También puedes empaquetarlo en zip:

```bash
zip -r MysteryBox.zip . -x "*.git*" "build/*"
```

Luego deja `MysteryBox.zip` en `plugins/` y reinicia el servidor.

## 🎮 Comandos y permisos

| Comando | Descripción | Permiso |
|---|---|---|
| `/mysterybox loot` | Abre los forms de gestión del loot (solo en juego) | `mysterybox.admin` |
| `/mysterybox give <jugador> [cantidad]` | Da cajas a un jugador | `mysterybox.admin` |
| `/mysterybox giveall [cantidad]` | Da cajas a todos los jugadores conectados | `mysterybox.admin` |

Alias: `/mbox`, `/crate`. **Permiso por defecto:** `op`.

## ⚙️ Configuración (`config.yml`)

```yaml
crate-name: '&l&bLucky &3Mystery &bBox'   # Nombre a mostrar (soporta códigos & de color)
prefix: '&l&6MysteryBox &r&7| '           # Prefijo de los mensajes
cooldown: 10                              # Segundos de espera entre aperturas
common-rewards: 3                         # Recompensas comunes por apertura
exclusive-rewards: 1                      # Recompensas exclusivas por apertura
```

> Las entradas serializadas del loot (`common-loot` / `exclusive-loot`) se gestionan en el juego mediante los forms. No las edites a mano.

## 🗂️ Estructura del proyecto

```
plugin.yml
resources/config.yml
src/mysterybox/
  MysteryBoxPlugin.php      clase principal, wiring
  command/MysteryBoxCommand.php
  crate/CrateFactory.php    ítem de la caja + lore
  crate/CrateOpener.php     rol y entrega de recompensas
  crate/CooldownTracker.php
  form/LootMenuForm.php     menú: común / exclusivo
  form/ManageLootForm.php   agregar item en mano / quitar / vaciar
  form/ConfirmClearForm.php
  listener/CrateListener.php
  loot/LootStorage.php      pools en memoria + persistencia
  loot/LootSerializer.php   serialización base64+NBT
  loot/LootType.php
```

## 📜 Licencia

MIT — ver [LICENSE](LICENSE).

## 👤 Autor

**joni**
