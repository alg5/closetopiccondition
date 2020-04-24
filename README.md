# Close topics by condition

## Installation

Copy the extension to phpBB/ext/alg/closetopiccondition

Go to "ACP" > "Customise" > "Extensions" and enable the "Limit posts in the intopic" extension.

## Tests and Continuous Integration

We use Travis-CI as a continuous integration server and phpunit for our unit testing. See more information on the [phpBB development wiki](https://wiki.phpbb.com/Unit_Tests).
To run the tests locally, you need to install phpBB from its Git repository. Afterwards run the following command from the phpBB Git repository's root:

Windows:

    phpBB\vendor\bin\phpunit.bat -c phpBB\ext\alg\closetopiccondition\phpunit.xml.dist

others:

    phpBB/vendor/bin/phpunit -c phpBB/ext/alg/closetopiccondition/phpunit.xml.dist

### Лицензия
[GNU General Public License v2](http://opensource.org/licenses/GPL-2.0)

Расширение позволяет администратору форума сформировать для любого форума условия закрытия тем в данном форуме:
- по количеству постов
- по отсутствию активности

В настройках можно детально задать условия закрытия, текст последнего поста, период проверки и т.п


Репозиторий: https://github.com/alg5/closetopiccondition
Инсталляция:
Скопируйте всё содержимое репозитория в папку ext/alg/closetopiccondition/

Перейдите в Панель администратора: АСР-> Персонализация-> Управление расширениями 
Включите расширение "closetopiccondition"
Поддерживаемые языки:
- Английский (TODО)
 Русский

### Лицензия
[GNU General Public License v2](http://opensource.org/licenses/GPL-2.0)

[![Build Status](https://travis-ci.org/alg5/closetopiccondition.svg?branch=master)](https://travis-ci.org/alg5/closetopiccondition)


