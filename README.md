# Qc workspace preview language

**Version française de la documentation, voir un peu plus bas**

This extension allow you to change the default language of the preview message in workspace (the top-right yellow box). Usually, users without any BE rights will get the message in English (default). The language you set MUST be installed for BE to make this works.

Use the ISO code 2 letters language. If the value doesn't exist, it will revert to default/English.

To use a different language in FE, you must add the TSconfig configuration, with the 2 letter language you want like this:

```bash
  mod.qcWsPreviewLang.used_lang = fr
```

## Documentation française

Cette extension permet de changer la langue par défaut du message de prévisualisation des espaces de travail (le message en jaune en haut à droite). Habituellement, pour un utilisateur de lien de prévisualisation, le message appraît en anglais s'il n'est pas connecté en BE. La langue configurée doit être installée pour fonctionner.

Utiliser les codes ISO de 2 lettres. Si la valeur n'existe pas, l'anglais sera utilisé pour le message.

Pour utiliser une langue différente en FE, vous devez ajouter une valeur dans la configuration TSconfig:

```bash
  mod.qcWsPreviewLang.used_lang = fr
```
