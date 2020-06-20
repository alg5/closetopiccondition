<?php
/**
*
* Thanks For Posts extension for the phpBB Forum Software package.
*
* @copyright (c) 2013 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(

	'ACP_CLOSETOPICCONDITION'						=> 'Ограничение числа постов в теме',
	'ACP_CLOSETOPICCONDITION'						=> 'Закрытие темы по условию',
	'ACP_CLOSETOPICCONDITION_FORUM'						=> 'Задать условия закрытия темы для форума',
	'ACP_CLOSETOPICCONDITION_SETTINGS'				=> 'Настройки форумные',
	'ACP_CLOSETOPICCONDITION_COMMON_SETTINGS'				=> 'Настройки общие',
	'ACP_CLOSETOPICCONDITION_YES'				=> 'Установить условия закрытия тем ',
	'ACP_CLOSETOPICCONDITION_NO'				=> 'Не устанавливать условия закрытия тем ',
	'ACP_CLOSEALLTOPICS_YES'				=> 'Закрывать все темы ',
	'ACP_CLOSEALLTOPICS_NO'				=> 'Закрывать только обычные(неприлепленные) темы ',
	'ACP_CLOSEALLTOPICS_NO_EXPLANE'				=> 'По умолчанию закрываются по условию только обычные темы, Выберите опцию "закрыть все темы",  для закрытия также прилепленных тем, важных тем и объявлений ',
	'ACP_CLOSE_BY_SOME_CONDITION'				=> 'Закрывать темы при выборе нескольких условий ',
	'ACP_CLOSE_BY_SOME_CONDITION_EXPLANE'				=> 'По умолчанию закрываются темы при наличии любого из условий. Выберите вторую опцию  для закрытия тем только в случае наступления всех выбранных условий ',
	'ACP_CLOSE_BY_EACH_CONDITION'				=> 'Закрывать при наличии любого из выбранных условий ',
	'ACP_CLOSE_BY_ALL_CONDITION'				=> 'Закрывать при наличии всех выбранных условий ',

	'ACP_CLOSETOPICCONDITION_OPTION'				=> 'Ограничить число постов в форуме ',
	'ACP_CLOSETOPICCONDITION_OPTION_EXPLAIN'				=> 'По наступлению любого из заданных условий форум будет закрыт задачей-кроном',
	'ACP_CLOSETOPICCONDITION_LIMIT_POSTS'				=> 'Закрыть тему после достижения заданного количества постов ',
	'ACP_CLOSETOPICCONDITION_INACTIVE_PERIOD_CONDITION'				=> 'Закрыть тему после отсутствия активности в течение заданного периода ',
	'ACP_CLOSETOPICCONDITION_SELECT'				=> 'Выберите форум',
	'ACP_CLOSETOPICCONDITION_LIVE_SEARCH_CAPTION'		=> 'Быстрый поиск',
	'ACP_CLOSETOPICCONDITION_EXPLAIN'				=> 'Выберите группы и/или отдельных пользователей для рассылки им уведомления <br /> Уведомление можно сформировать или выбрать из имеюзихся сохраненных шаблонов ',
	'ACP_CLOSETOPICCONDITION_SEARCH_USER'				=> 'имя пользователя...',
	'ACP_CLOSETOPICCONDITION_SEARCH_USER_TOOLTIP'				=> 'Для быстрого поиска начинайте печатать имя пользователя',
	'ACP_CLOSETOPICCONDITION_SEARCH_GROUP'				=> 'группа...',
	'ACP_CLOSETOPICCONDITION_SEARCH_GROUP_TOOLTIP'				=> 'Для быстрого поиска начинайте печатать название группы',
	'ACP_CLOSETOPICCONDITION_SEARCH_FORUM'				=> 'форум...',
	'ACP_CLOSETOPICCONDITION_SEARCH_FORUM_TOOLTIP'				=> 'Для быстрого поиска начинайте печатать название форума',
	'ACP_CLOSETOPICCONDITION_NUMBER'				=> 'Введите число для ограничения количества постов',
	'ACP_CLOSETOPICCONDITION_NUMBER_EXPLAIN'				=> 'После достижения данного количества постов задача-крон закроет тему <br />Введите 0 для отключения этой возможности или снимите галочку',
	'ACP_CLOSETOPICCONDITION_INACTIVE_PERIOD'				=> 'Введите число (месяцев)',
	'ACP_CLOSETOPICCONDITION_INACTIVE_PERIOD_EXPLAIN'				=> 'При отсутствии активности в теме в течение заданного периода задача-крон закроет тему <br />Введите 0 для отключения этой возможности  или снимите галочку',
	'ACP_CLOSETOPICCONDITION_NUMBER_OPTIONS'				=> 'Опции для закрытия тем по условию',
	'ACP_CLOSETOPICCONDITION_PERIOD'				=> 'Выберите период запуска задачи-крона',
	'ACP_CLOSETOPICCONDITION_PERIOD_EXPLAIN'				=> 'По умолчанию задача крон будет запускаться  1 раз в сутки<br />Для очень посещаемых форумов рекомендуется запускать задачу-крон чаще',
	'ACP_CLOSETOPICCONDITION_PERIOD_0'				=> 'Один раз в сутки',
	'ACP_CLOSETOPICCONDITION_PERIOD_1'				=> 'Два раза в сутки',
	'ACP_CLOSETOPICCONDITION_PERIOD_2'				=> 'Четыре раза в сутки',
	'ACP_CLOSETOPICCONDITION_PERIOD_3'				=> 'Один раз в час',
	'ACP_CLOSETOPICCONDITION_FORUM_SELECTED'				=> 'Форум <b>%s</b>',
	'ACP_CLOSETOPICCONDITION_FORUM_SELECTED'				=> 'Форум',
	'ACP_CLOSETOPICCONDITION_LAST_POST'				=> 'Последний пост',
	'ACP_CLOSETOPICCONDITION_LAST_POST_EXPLAIN'				=> 'Если опция выбрана, этот пост будет последним перед закрытием темы',
	'ACP_CLOSETOPICCONDITION_LAST_POST_SETTING'				=> 'Настройки последнего поста',
	'ACP_CLOSETOPICCONDITION_LAST_POST_TEXT'				=> 'Текст последнего поста',
	'ACP_CLOSETOPICCONDITION_LAST_POST_TEXT_EXPLAIN'				=> 'Можно использовать бб-коды и смайлы',
	'ACP_CLOSETOPICCONDITION_LAST_POST_TEXT_EXPLAIN2'				=> '<br />Если оставить поле пустым, последнего поста не будет ',
	'ACP_CLOSETOPICCONDITION_LAST_POST_USER'				=> 'Пользователь',
	'ACP_CLOSETOPICCONDITION_LAST_POST_USER_EXPLAIN'				=> 'Выбранный пользователь будет автором(постером?) последнего поста темы',
	'ACP_CLOSETOPICCONDITION_LAST_POST_SENDER'				=> 'Пользователь',
	'ACP_CLOSETOPICCONDITION_LAST_POST_SENDER_EXPLAIN'				=> 'От имени выбранного пользователя будут посылаться уведомления',
	'ACP_CLOSETOPICCONDITION_NOTY_SEND'				=> 'Уведомления',
	'ACP_CLOSETOPICCONDITION_NOTY_SEND_EXPLAIN'				=> 'Если опция выбрана, будут разосланы уведомления о закрытии темы',
	'ACP_CLOSETOPICCONDITION_NOTY_SEND_SETTING'				=> 'Настройки уведомлений',
	'ACP_CLOSETOPICCONDITION_NOTY_SEND_TOPICPOSTER'				=> 'Текст уведомления автору темы',
	'ACP_CLOSETOPICCONDITION_NOTY_SEND_MODERATORS'				=> 'Текст уведомления модераторам форума',
	'ACP_CLOSETOPICCONDITION_NO_USERS'				=> 'Пользователь <b>%s</b> не существует, неактивен или забанен',
	'ACP_CLOSETOPICCONDITION_GET_DATA'				=> 'Получить данные',
	'ACP_CLOSETOPICCONDITION_SAVE_DATA'				=> 'Сохранить',
	'ACP_CLOSETOPICCONDITION_SAVED'				=> 'Опции форума сохранены успешно',
	'ACP_CLOSETOPICCONDITION_DELETED'				=> 'Опции форума удалены',
	'ACP_CLOSETOPICCONDITION_PERIOD_SAVED'				=> 'Период запуска задачи-крона сохранен успешно',
	'ACP_CLOSETOPICCONDITION_MONTHES'				=> 'месяцев',
	'ACP_CLOSETOPICCONDITION_GENERAL_TOPICS'				=> 'закрывать только обычные темы',
	'ACP_CLOSETOPICCONDITION_ALL_TOPICS'				=> 'закрывать все темы',
	'ACP_CLOSETOPICCONDITION_REMOVE_TO_ARCHIVE'				=> 'Переносить закрытые темы в архив',

	'NOTIFICATION_TOPICPOSTER'				=> 'Тема %s закрыта',
	'NOTIFICATION_TOPICPOSTER'				=> 'Тема закрыта',
	'NOTIFICATION_MODER'				=> 'Тема закрыта',
	'CLOSETOPICCONDITION_NOTIFICATION_REFERENCE'			=> '«%1$s»',
	'NOTIFICATION_TYPE_TOPICPOSTER'				=> 'Увеломление автора о закрытии темы по лимиту постов',
	'NOTIFICATION_TYPE_MODER'				=> 'Увеломление модератора о закрытии темы по лимиту постов',

));
