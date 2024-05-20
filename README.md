# tempo

#### Codebase Setup:

```
composer install
npm install
```

> [!IMPORTANT]
> Customize the values of the variables located in the **includes/config.php** file.

#### Database Setup:

```
CREATE TABLE `timesheet` (
  `index` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `issue_key` varchar(10) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `time_spent` time DEFAULT NULL,
  `started` datetime DEFAULT current_timestamp(),
  `synced` int(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

ALTER TABLE `timesheet`
  ADD PRIMARY KEY (`index`);

ALTER TABLE `timesheet`
  MODIFY `index` int(11) NOT NULL AUTO_INCREMENT;
```

#### Webpack in Production Mode:

```
npm run build
```

#### Webpack in Development Mode:

```
npm run build:watch
```
