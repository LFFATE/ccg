![GitHub](https://img.shields.io/github/license/LFFATE/ccg.svg)
![GitHub closed pull requests](https://img.shields.io/github/issues-pr-closed-raw/lffate/ccg.svg)
![GitHub issues](https://img.shields.io/github/issues-raw/lffate/ccg.svg)
![GitHub (Pre-)Release Date](https://img.shields.io/github/release-date-pre/lffate/ccg.svg)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/LFFATE/ccg.svg)](https://scrutinizer-ci.com/g/LFFATE/ccg/?branch=4.9)
[![codecov](https://codecov.io/gh/LFFATE/ccg/branch/4.9/graph/badge.svg)](https://codecov.io/gh/LFFATE/ccg)
[![Build Status](https://travis-ci.org/LFFATE/ccg.svg?branch=4.9)](https://travis-ci.org/LFFATE/ccg)

# Cs-cart code generator
- [Cs-cart code generator](#cs-cart-code-generator)
  - [Purposes](#purposes)
  - [Features](#features)
  - [Getting started](#getting-started)
    - [Installing](#installing)
    - [Configure](#configure)
  - [**Note: wrap the value by quotes if it contains spaces, commas and other non-word charachters**](#note-wrap-the-value-by-quotes-if-it-contains-spaces-commas-and-other-non-word-charachters)
    - [Generate](#generate)
    - [Autocomplete](#autocomplete)
      - [Linux](#linux)
      - [Windows](#windows)
    - [Test](#test)
  - [Contributing :fire:](#contributing-fire)
  - [TODO](#todo)
  - [Settings](#settings)


## Purposes

---
## Features

---
## Getting started
### Installing
```
~: git clone https://github.com/LFFATE/ccg.git
~: cd ccg
```
---
### Configure
Open `config/custom.php` and override config values:
```
<?php

$customs['addon'] = [
    'id' => 'my_default_addon_name'
];

```
All other configs you can find at `config/defaults.php`, `config/filesystem.php` files (**Don't change them**) and others.

All this options you also can set by command line:
```
~: ./ccg.php addon create --addon.id custom_addon_id 
```
**Note: wrap the value by quotes if it contains spaces, commas and other non-word charachters**
---
### Generate
Get help
```
~: cd /path/to/ccg
~: ./ccg.php help # for Linux
~: php ccg.php # for Windows
```

---
### Autocomplete
#### Linux
Copy script for autocomplition:
```
~: sudo cp ccg /etc/bash_completion.d/ccg
```
Update bash:
```
~: source ~/.bashrc
```
Use autocomplete:
```
~: ./ccg.php [Tab][Tab]
```
#### Windows
Not available

---
### Test
```
~: cd /path/to/ccg
~: phpunit --testdox
```
---
## Contributing :fire:
- Fork
- Code
- Pull request


---
## TODO
- ~~Handle addonXml remove settings~~ :heavy_check_mark:
- Change namespaces to PSR-0
- Improve tests filesystem (split to directories)
- Copy files from resources to new addon destination on create addon command (ResourcesGenerator)
- ~~Add method to LanguageGenerator to remove langvars by setting id (all variants for the setting and etc.)~~ :heavy_check_mark:
- Improve tests coverage
- Add filesystem decorator for save history of files to be edited/removed


---
## Settings
|Name|Description|Code|Default|
| --- | --- | --- | --- |
|**Addon**|
|Name|Addon name (id)|addon.id|sd_new_addon|
|Scheme||addon.scheme|3.0|
|Edition type||addon.edition_type|ROOT,ULT:VENDOR|
|Version||addon.version|4.8.1|
|Priority||addon.priority|1000|
|Position||addon.position|1|
|Status||addon.status|active|
|Has icon||addon.has_icon|Y|
|Default language||addon.default_language|en|
|Supplier||addon.supplier|Simtech Development|
|Supplier link||addon.supplier_link|http://www.simtechdev.com|
|Auto install||addon.auto_install|MULTIVENDOR,ULTIMATE|
|**developer**|
|name||developer.name|John Doe|
|company||developer.company|Simtech|
|**filesystem**|
|Output path|Path to which the generator will place add-ons.|filesystem.output_path|/home/ccg/cscart/${addon.id}/|
