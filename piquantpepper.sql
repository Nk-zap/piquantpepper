-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: MySQL-5.7
-- Время создания: Дек 13 2025 г., 14:50
-- Версия сервера: 5.7.44
-- Версия PHP: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `piquantpepper`
--

-- --------------------------------------------------------

--
-- Структура таблицы `requests`
--

CREATE TABLE `requests` (
  `id` int(11) NOT NULL,
  `contacts` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  `bronir` enum('1','2','3','4','5','6','7','8','9','10') COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment` enum('наличные','карта') COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('Новое','Посещение состоялось','Отменено') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Новое',
  `user_id` int(11) NOT NULL,
  `feedback` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `requests`
--

INSERT INTO `requests` (`id`, `contacts`, `date`, `bronir`, `payment`, `status`, `user_id`, `feedback`) VALUES
(1, 'почта ivan@gmail.com', '2024-09-24 02:26:00', '2', 'наличные', 'Новое', 2, ''),
(2, 'телефон 88005553535', '2024-09-30 15:30:00', '5', 'карта', 'Посещение состоялось', 2, ''),
(3, '<i>попытка XSS :)</i>', '2024-09-30 21:21:00', '3', 'карта', 'Отменено', 4, ''),
(4, '9653780921', '2004-03-04 23:34:00', '3', 'наличные', 'Новое', 1, ''),
(5, '9653780921', '2025-02-12 23:02:00', '1', 'наличные', 'Посещение состоялось', 6, ''),
(6, '9574848484', '2025-03-22 23:59:00', '1', 'наличные', 'Новое', 6, '');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `login` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fullname` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `email`, `login`, `password`, `fullname`, `phone`) VALUES
(1, 'moynesam@gmail.com', 'admin', 'restaurant', 'Admin Adminovich', ''),
(2, 'ivan@gmail.com', 'ivan', '1234', 'Иван Иванович Иванов', '88005553535'),
(4, 'oleg@gmail.com', 'oleg', '1234', 'Олег Олегович Олегов', '88005553535'),
(6, 'kir@mail.ru', 'kerais', 'лукф123', 'Богданова Кэра Олеговна', '+79653780921');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `requests`
--
ALTER TABLE `requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
