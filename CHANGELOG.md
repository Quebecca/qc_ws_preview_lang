# Qc Workspace Preview Language

## ⚠️ Important Notice – TYPO3 v13

**This extension will NOT be available for TYPO3 v13 or higher.**

### Reason
Starting with **TYPO3 v13**, the core class`TYPO3\CMS\Workspaces\Middleware\WorkspacePreview`is declared as **final**, which makes class overriding impossible.

Since this extension relies on overriding the Workspace Preview behavior, it **cannot be technically supported** anymore.

👉 **The last supported TYPO3 version is: TYPO3 v12 (LTS)**  
👉 **No further updates are planned beyond v12**

## Compatibility

| TYPO3 Version | Supported |
|--------------|-----------|
| TYPO3 v11    | ❌ No |
| TYPO3 v12    | ✅ Yes (last supported version) |
| TYPO3 v13+   | ❌ No (final classes prevent override) |

---

## Change Log

### v2.0.2
- 2025-02-06 [Fix] Remove unused file and code.

### v2.0.1
- 2024-12-23 [Fix] Bug fixes.

### v2.0.0
- 2024-12-23 [FEATURE] Add Support for Typo3 V12.

### v1.1.0

#### ⚠️ Breaking Changes
- **2023-03-27**
- [!!!] Switched from **TypoScript** to **TSconfig** for defining the ISO language code used in the Workspace Preview message.


#### Tasks
- **2023-03-27**
- [Feature] Automatically detect the language from **TSconfig**.
- [Feature] Translate the “go back to live page” message in Workspace Preview based on detected language.


## License
GPL-2.0-or-later