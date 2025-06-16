<p align="center">
  <img src="https://img.shields.io/badge/plugin-ActionHouse-blueviolet?style=for-the-badge">
  <br><br>
  <a href="https://paypal.me/FrostCheatMC?country.x=CO&locale.x=es_XC">
    <img src="https://img.shields.io/badge/donate-paypal-ff69b4?style=for-the-badge&logo=paypal">
  </a>
  <a href="https://poggit.pmmp.io/ci/FrostCheatMC/ActionHouse/ActionHouse">
    <img src="https://poggit.pmmp.io/ci.shield/FrostCheatMC/ActionHouse/ActionHouse?style=for-the-badge">
  </a>
  <a href="https://poggit.pmmp.io/p/ActionHouse">
    <img src="https://poggit.pmmp.io/shield.downloads/ActionHouse?style=for-the-badge">
  </a>
</p>

<h1 align="center">📦 ActionHouse</h1>
<p align="center">A powerful Auction House plugin for PocketMine-MP, ShulkerBox Viewer support, multi-language, NPC interaction, and lag-free performance!</p>

---

## ✨ Features

- ✅ Fully customizable messages via `language` files
- 💸 Economy support with **BedrockEconomy**
- 🧪 Compatible with latest **PocketMine-MP API**
- 🌍 Multi-language system (es-ES, en-US, fr-FR, etc.)
- 📦 Support for **ShulkerBox item content viewer**
- 💰 Min & Max price configuration per item
- 🎯 Limit max items per player
- ⏱️ Configurable item expiration/duration
- ⚡ Optimized for performance — no lag, even with large data
- 📚 Paginated inventory menus
- ✅ Confirm Buy system
- 👤 NPC support to open `/ah` menu
- 🔒 Permission-based control

---

## 🧱 Supported Software

> ✅ This plugin is only compatible with **PocketMine-MP**  
> ❌ It will NOT work on Nukkit, Altay, or other forks

---

## 📥 Installation

1. 📦 [Download ActionHouse](https://poggit.pmmp.io/p/ActionHouse) from Poggit
2. 💰 [Download BedrockEconomy](https://poggit.pmmp.io/p/BedrockEconomy/)
3. 📁 Place both `.phar` files inside your `/plugins/` directory
4. 🔁 Restart your server
5. ✅ Ready to go! Use `/ah` to open the auction menu

---

## 📜 Commands

| Command                                                       | Description                                    |
|---------------------------------------------------------------|------------------------------------------------|
| `/actionhouse` or `/ah`                                       | Open the main auction house menu               |
| `/actionhouse sell [price]` or `/ah sell [price]`             | Sell the item in hand for the given price      |
| `/actionhouse setlanguage [lang]` or `/ah setlanguage [lang]` | Change the plugin language (e.g., `en-US`)     |
| `/actionhouse npc` or `/ah npc`                               | Spawn a custom NPC that opens the auction menu |

---

## ⚙️ Configuration

Once installed, the plugin generates the following files:

- `config.yml` – General configuration (max items, min/max prices, etc.)
- `items.yml` – Auctioned item data (auto-managed)
- `/language/` – Translatable strings (`en-US.yml`, `es-ES.yml`, etc.)

You can edit `lang` files to fully customize messages and colors.

---

## 👤 NPC System

Spawn an NPC using:

```
/ah npc
```

This NPC allows players to open the auction menu just by clicking it.
To **remove the NPC**, hit it with a **Bedrock block**
(Requires permission: `actionhouse.command.npc`)

---

## 🌍 Supported Languages

You can switch the plugin language at any time:

```
/ah setlanguage en-US
```

Supported languages:

* 🇺🇸 English (`en-US`)
* 🇪🇸 Español (`es-ES`)
* 🇫🇷 Français (`fr-FR`)
* 🇧🇷 Português (`pr-BR`)
* 🇩🇪 Deutsch (`de-DE`)
* 🇷🇺 Русский (`ru-RU`)

Feel free to contribute more in `/language/`.

---

## 🧑‍💻 Developer Notes

* Uses [InvMenu](https://github.com/Muqsit/InvMenu) for inventory GUIs
* All data is serialized/deserialized and saved using optimized logic
* Supports **ShulkerBox viewing** directly from confirm menu inventory
* Saves data **asynchronously** to avoid lag on high-load servers

---

## 📖 License

Licensed under the [MIT License](https://github.com/FrostCheatMC/ActionHouse/blob/master/LICENSE)
You are free to fork, contribute, or suggest changes.

---

## ☕ Support & Donate

If this plugin helped you, or you want to support future updates:

> 💖 [Donate via PayPal](https://paypal.me/FrostCheatMC?country.x=CO&locale.x=es_XC)

Any support is greatly appreciated!

---

<p align="center"><b>Made with 💙 by FrostCheatMC</b></p>