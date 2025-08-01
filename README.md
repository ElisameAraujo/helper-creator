# 🛠️ HelperCreator

**HelperCreator** é um pacote para Laravel que facilita o gerenciamento e autoload de helpers personalizados. Ele automatiza o registro no `composer.json`, cria backups e mantém seu projeto limpo e funcional.

---

## 🚀 Features

-   ✅ Registra automaticamente arquivos helper na chave `autoload.files` do `composer.json`
-   🧠 Cria e gerencia backups das últimas 3 versões do `composer.json`
-   🧹 Comando inteligente para limpar entradas inválidas
-   🔄 Restaura backups com segurança
-   🧱 Compatível com Laravel 11+

---

## 📋 Requirements

-   PHP >= 8.1
-   Laravel >= 11.0

---

## 📦 Installation

```bash
composer require elisame/helper-creator
```

## ⚙️Configurações

Você pode rodar o comando para publicar o arquivo de configurações.

```
php artisan vendor:publish --tag=helper-creator-config
```

---

## ⚙️ Usage

### ✨ Creating a new helper

```
php artisan helper:create MyNewHelper
```

Isso criará o arquivo em `app/Helpers` e o registrará automaticamente no `composer.json`.

### ♻️ Restaurar o último backup do composer.json

```
php artisan helper:restore-backup
```

Restaura a versão anterior do `composer.json` e atualiza o autoload.

## 🧹 Limpar entradas inválidas do autoload

```
php artisan helper:cleanup
```

Remove arquivos que foram excluídos manualmente mas que ainda estão listados no composer.json. Você pode usar a flag `--dry-run` para verificar o que será removido do arquivo `composer.json` sem fazer alterações.

## 📁 Estrutura gerada

Quando você criar um novo helper, você terá a seguinte estrutura dentro da raiz do seu projeto.

```
app/
└── Helpers/
    └── MyNewHelper.php

composer.json
└── autoload.files
    └── "app/Helpers/MyNewHelper.php"`
```

## 🛡️ Segurança

Antes de qualquer alteração no composer.json, o pacote cria backups automáticos em:

```
storage/helper-creator/backups/composer
```

Você pode restaurar qualquer versão anterior com segurança.
