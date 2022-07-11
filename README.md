# Qc workspace preview language
This extension is allow you to change the default language of the preview message in workspace, for example from default change to de, fr or any other installed language, you just sould make sure this language are installed in typo3.

<br>

An example from typo3 constant to set the default language, you should set the ISO code of language, in case of lang not available or worng ISO code, they will use the 'default' language of system by default.


```bash
plugin.tx_qc_ws_preview_lang{
    used_language = fr
}
```