# 🎁 MysteryBox

Mystery crate plugin for **PocketMine-MP (API 5)** with **form-based reward management** — no chest GUIs, no external dependencies.

**Author:** joni

---

## ✨ Features

- Custom mystery crate item (nether star, unplaceable, shows the possible drops in its lore).
- Right-click to open: consumes one crate, rolls common + exclusive rewards, drops overflow on the ground.
- Configurable cooldown per player.
- **Loot management entirely through forms** (`/mysterybox loot`):
  - Add the exact stack you are holding (`+ Add item in hand`)
  - Remove a specific reward from the list
  - Clear a whole pool (with confirmation dialog)
- Configurable reward counts, messages and crate name (`config.yml`).
- Loot is stored serialized (base64 + NBT) inside `config.yml`.
- Zero external dependencies.

## 📦 Installation

1. Download or clone this repository.
2. Place the plugin folder (or a phar/zip build) into your server's `plugins/` directory.
3. Start the server once to generate `config.yml`.
4. In-game, run `/mysterybox loot` and add items from your hand to the **Common** and **Exclusive** pools.
5. Give crates with `/mysterybox give <player>` or `/mysterybox giveall`.

## 🛠️ Building

For development, PMMP-MP can load the plugin folder directly. You can also zip/phar it:

```bash
zip -r MysteryBox.zip . -x "*.git*" "build/*"
```

Then drop `MysteryBox.zip` into `plugins/` and restart the server.

## 🎮 Commands & Permissions

| Command | Description | Permission |
|---|---|---|
| `/mysterybox loot` | Open the loot management forms (in-game only) | `mysterybox.admin` |
| `/mysterybox give <player> [amount]` | Give crates to a player | `mysterybox.admin` |
| `/mysterybox giveall [amount]` | Give crates to everyone online | `mysterybox.admin` |

Aliases: `/mbox`, `/crate`.  **Default permission:** `op`.

## ⚙️ Configuration (`config.yml`)

```yaml
crate-name: '&l&bLucky &3Mystery &bBox'   # Display name (supports & color codes)
prefix: '&l&6MysteryBox &r&7| '           # Message prefix
cooldown: 10                              # Seconds between openings
common-rewards: 3                         # Common rewards per opening
exclusive-rewards: 1                      # Exclusive rewards per opening
```

> The serialized loot entries (`common-loot` / `exclusive-loot`) are managed in-game via the forms. Don't edit them by hand.

## 🗂️ Project layout

```
plugin.yml
resources/config.yml
src/mysterybox/
  MysteryBoxPlugin.php      main class, wiring
  command/MysteryBoxCommand.php
  crate/CrateFactory.php    crate item + lore
  crate/CrateOpener.php     reward roll + delivery
  crate/CooldownTracker.php
  form/LootMenuForm.php     menu: common / exclusive
  form/ManageLootForm.php   add held item / remove / clear
  form/ConfirmClearForm.php
  listener/CrateListener.php
  loot/LootStorage.php      in-memory pools + persistence
  loot/LootSerializer.php   base64+NBT (de)serialization
  loot/LootType.php
```

## 📜 License

MIT — see [LICENSE](LICENSE).

## 👤 Author

**joni**
