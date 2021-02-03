
--
-- Table structure for table `fetched_file_batches`
--

CREATE TABLE `fetched_file_batches` (
  `id` bigint(20) NOT NULL,
  `server_id` varchar(255) DEFAULT NULL COMMENT 'ID associated with this server (set in globals)',
  `start_datetime` datetime DEFAULT NULL,
  `end_datetime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `fetched_file_files`
--

CREATE TABLE `fetched_file_files` (
  `id` bigint(20) NOT NULL,
  `batch_id` bigint(20) NOT NULL,
  `filename` varchar(512) NOT NULL,
  `filesize` int(11) NOT NULL,
  `status` varchar(255) NOT NULL,
  `date_created` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fetched_file_files_meta`
--

CREATE TABLE `fetched_file_files_meta` (
  `id` bigint(20) NOT NULL,
  `file_id` bigint(20) NOT NULL,
  `meta_key` varchar(255) NOT NULL,
  `meta_value` text,
  `options` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `fetched_file_batches`
--
ALTER TABLE `fetched_file_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fetched_file_files`
--
ALTER TABLE `fetched_file_files`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fetched_file_files_meta`
--
ALTER TABLE `fetched_file_files_meta`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `fetched_file_batches`
--
ALTER TABLE `fetched_file_batches`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fetched_file_files`
--
ALTER TABLE `fetched_file_files`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fetched_file_files_meta`
--
ALTER TABLE `fetched_file_files_meta`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;


