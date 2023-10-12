CREATE DATABASE IF NOT EXISTS `elms`;
-- ---------------------
-- User oriented tables |
-- ---------------------

-- Creating roles table
CREATE TABLE `elms`.`roles` ( 
    `id` INT NOT NULL AUTO_INCREMENT, 
    `name` VARCHAR (255) NOT NULL , 
    PRIMARY KEY (`id`), 
    UNIQUE (`name`)
) ENGINE = InnoDB;

-- Creating a table for the programs
CREATE TABLE `elms`.`programs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `alias` varchar (255) NULL,
    `degree` enum('HND','BTECH') NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Creating the users table
CREATE TABLE `elms`.`users` ( 
    `id` INT NOT NULL AUTO_INCREMENT , 
    `lname` VARCHAR(60) NOT NULL , 
    `oname` VARCHAR(60) NOT NULL , 
    `username` VARCHAR(60) NOT NULL , 
    `user_role` INT NOT NULL , 
    PRIMARY KEY (`id`),
    UNIQUE (`username`),
    INDEX (`user_role`),
    FOREIGN KEY (`user_role`) REFERENCES `elms`.`roles`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

-- creating the students reference table
CREATE TABLE `elms`.`students` ( 
    `index_number` VARCHAR(15) NOT NULL , 
    `user_id` INT NOT NULL ,
    `level` INT NOT NULL,
    `program_id` INT NOT NULL,
    PRIMARY KEY (`index_number`), 
    UNIQUE (`user_id`),
    FOREIGN KEY (`user_id`) REFERENCES `elms`.`users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`program_id`) REFERENCES `elms`.`programs`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

-- Creating the user login table
CREATE TABLE `elms`.`userlogin` ( 
    `user_id` INT NOT NULL , 
    `username` VARCHAR(60) NOT NULL , 
    `password` VARCHAR(120) NOT NULL,
    PRIMARY KEY (`user_id`),
    FOREIGN KEY (`user_id`) REFERENCES `elms`.`users`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE = InnoDB;

-- Creating the user activity log table
CREATE TABLE `elms`.`activity` ( 
    `id` INT NOT NULL AUTO_INCREMENT , 
    `user_id` INT NOT NULL , 
    `action` ENUM('login','logout','page','link') NOT NULL , 
    `action_content` TEXT NULL , 
    `timestamp` DATETIME NOT NULL , 
    PRIMARY KEY (`id`), 
    INDEX `activity_user` (`user_id`),
    FOREIGN KEY (`user_id`) REFERENCES `elms`.`users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

-- ----------------------------
-- end of user oriented tables |
-- ____________________________|

-- -----------------------
-- course oriented tables |
-- -----------------------

-- Creating the courses table
CREATE TABLE `elms`.`courses` ( 
    `id` INT NOT NULL AUTO_INCREMENT , 
    `course_name` VARCHAR(255) NOT NULL , 
    `course_alias` VARCHAR(255) DEFAULT NULL , 
    `course_code` VARCHAR(255) NOT NULL,
    `instructor_id` INT NOT NULL ,
    `program_id` INT NOT NULL,
    PRIMARY KEY (`id`),
    Index (`instructor_id`,`program_id`),
    UNIQUE (`course_code`),
    FOREIGN KEY (`instructor_id`) REFERENCES `elms`.`users`(`id`) ON UPDATE CASCADE,
    FOREIGN KEY (`program_id`) REFERENCES `elms`.`programs`(`id`) ON UPDATE CASCADE
) ENGINE = InnoDB;

-- Creating the course materials table
CREATE TABLE `elms`.`coursematerials` ( 
    `id` INT NOT NULL AUTO_INCREMENT , 
    `course_id` INT NOT NULL , 
    `material_type` VARCHAR(255) NOT NULL , 
    `material_path` VARCHAR(255) NOT NULL , 
    PRIMARY KEY (`id`),
    INDEX (`course_id`),
    FOREIGN KEY (`course_id`) REFERENCES `elms`.`courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

-- ------------------------------
-- end of course oriented tables |
-- ______________________________|

-- -----------------------
-- Tables for assessments |
-- -----------------------

-- Creating the assignment table
CREATE TABLE `elms`.`assignments` ( 
    `id` INT NOT NULL AUTO_INCREMENT , 
    `course_id` INT NOT NULL ,
    `title` VARCHAR(80) NOT NULL , 
    `description` TEXT NOT NULL , 
    `instructor_id` INT NOT NULL , 
    `program_id` INT NOT NULL ,
    `program_level` INT NOT NULL ,
    `material_ids` VARCHAR(50) NULL , 
    `start_date` DATETIME NOT NULL DEFAULT NOW() , 
    `end_date` DATETIME NOT NULL , 
    `active` BOOLEAN NOT NULL DEFAULT FALSE , 
    PRIMARY KEY (`id`),

    Index `assignments_idx_course_instructor_program` (`course_id`,`instructor_id`, `program_id`),
    FOREIGN KEY (`course_id`) REFERENCES `elms`.`courses`(`id`) ON UPDATE CASCADE,
    FOREIGN KEY (`program_id`) REFERENCES `elms`.`programs`(`id`) ON UPDATE CASCADE,
    FOREIGN KEY (`instructor_id`) REFERENCES `elms`.`users`(`id`) ON UPDATE CASCADE
) ENGINE = InnoDB;

-- Creating the quizzes table
CREATE TABLE `elms`.`quizzes` ( 
    `id` INT NOT NULL AUTO_INCREMENT , 
    `instructor_id` INT NOT NULL , 
    `course_id` INT NOT NULL , 
    `title` VARCHAR(255) NOT NULL , 
    `description` TEXT NOT NULL , 
    `program_id` INT NOT NULL ,
    `program_level` INT NOT NULL ,
    `start_date` DATETIME NOT NULL , 
    `end_time` DATETIME NOT NULL , 
    `active` BOOLEAN NOT NULL DEFAULT FALSE , 
    PRIMARY KEY (`id`), 
    INDEX `quizzes_idx_course_instructor_program` (`course_id`,`instructor_id`, `program_id`),
    FOREIGN KEY (`course_id`) REFERENCES `elms`.`courses`(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (`program_id`) REFERENCES `elms`.`programs`(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (`instructor_id`) REFERENCES `elms`.`users`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE = InnoDB;

-- Creating the questions table
CREATE TABLE `elms`.`questions` ( 
    `id` INT NOT NULL AUTO_INCREMENT , 
    `quiz_id` INT NOT NULL , 
    `question_type` ENUM('text','radio','checkbox') NOT NULL , 
    `question_text` TEXT NOT NULL , 
    `question_image` VARCHAR(255) NULL DEFAULT NULL , 
    PRIMARY KEY (`id`), 
    INDEX `questions_quiz` (`quiz_id`),
    FOREIGN KEY (`quiz_id`) REFERENCES `elms`.`quizzes`(`id`)
) ENGINE = InnoDB;

-- Creating a table for the options for questions [usually select options]
CREATE TABLE `elms`.`questionoptions` ( 
    `id` INT NOT NULL AUTO_INCREMENT , 
    `question_id` INT NOT NULL , 
    `content` VARCHAR(255) NOT NULL , 
    PRIMARY KEY (`id`), 
    INDEX `questionoptions_question` (`question_id`),
    FOREIGN KEY (`question_id`) REFERENCES `elms`.`questions`(`id`)
) ENGINE = InnoDB;

-- Creating a submission table
CREATE TABLE `elms`.`submissions` ( 
    `id` INT NOT NULL AUTO_INCREMENT , 
    `work_type` ENUM('quiz','assignment') NOT NULL ,
    `work_id` INT NOT NULL,
    `student_id` INT NOT NULL , 
    `submission_time` DATETIME NOT NULL , 
    `content` TEXT NOT NULL , 
    PRIMARY KEY (`id`), 
    INDEX `submissions_student` (`student_id`),
    FOREIGN KEY (`student_id`) REFERENCES `elms`.`users`(`id`) ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE `elms`.`grades` ( 
    `id` INT NOT NULL AUTO_INCREMENT ,  
    `work_type` ENUM('quiz','assignment') NOT NULL ,
    `work_id` INT NOT NULL,
    `student_id` INT NOT NULL , 
    `score` DECIMAL(5,2) NOT NULL , 
    PRIMARY KEY (`id`), 
    INDEX `grade_student` (`student_id`),
    FOREIGN KEY (`student_id`) REFERENCES `elms`.`users`(`id`)
) ENGINE = InnoDB;

-- -------------------------
-- end of assessment tables |
-- _________________________|

-- -------------------------------
-- Discussion and messages tables |
-- -------------------------------

-- Create a discussion table
CREATE TABLE `elms`.`discussions` ( 
    `id` INT NOT NULL AUTO_INCREMENT , 
    `course_id` INT NOT NULL , 
    `user_id` INT NOT NULL , 
    `content` TEXT NOT NULL , 
    `post_time` DATETIME NOT NULL , 
    PRIMARY KEY (`id`), 
    INDEX `discussions_course_user` (`course_id`, `user_id`),
    FOREIGN KEY (`course_id`) REFERENCES `elms`.`courses`(`id`) ON UPDATE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `elms`.`users`(`id`) ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE `elms`.`messages` ( 
    `id` INT NOT NULL AUTO_INCREMENT , 
    `sender_id` INT NOT NULL , 
    `recepient_id` INT NOT NULL , 
    `content` TEXT NOT NULL , 
    `message_time` DATETIME NOT NULL , 
    PRIMARY KEY (`id`), 
    INDEX `messages_user` (`sender_id`, `recepient_id`),
    FOREIGN KEY (`sender_id`) REFERENCES `elms`.`users`(`id`) ON UPDATE CASCADE,
    FOREIGN KEY (`recepient_id`) REFERENCES `elms`.`users`(`id`) ON UPDATE CASCADE
) ENGINE = InnoDB;

-- --------------------------------------
-- End of discussion and messages tables |
-- ______________________________________|

-- ------------------------------------
-- Video conferencing [future project] |
-- ------------------------------------

-- Creating a video conference table
CREATE TABLE `elms`.`videoconference` ( 
    `id` INT NOT NULL AUTO_INCREMENT , 
    `course_id` INT NOT NULL , 
    `instructor_id` INT NOT NULL , 
    `conference_name` VARCHAR(255) NOT NULL , 
    `description` MEDIUMTEXT NULL , 
    `conference_date` DATETIME NOT NULL , 
    `duration` DECIMAL(3,1) NOT NULL , 
    PRIMARY KEY (`id`), 
    INDEX `video_course_instructor` (`course_id`, `instructor_id`),
    FOREIGN KEY (`course_id`) REFERENCES `elms`.`courses`(`id`) ON UPDATE CASCADE,
    FOREIGN KEY (`instructor_id`) REFERENCES `elms`.`users`(`id`) ON UPDATE CASCADE
) ENGINE = InnoDB;

-- Creating a participants table for the conference
CREATE TABLE `elms`.`participants` ( 
    `id` INT NOT NULL AUTO_INCREMENT , 
    `conference_id` INT NOT NULL , 
    `user_id` INT NOT NULL , 
    PRIMARY KEY (`id`), 
    INDEX `participants_conf_user` (`conference_id`, `user_id`),
    FOREIGN KEY (`user_id`) REFERENCES `elms`.`users`(`id`) ON UPDATE CASCADE,
    FOREIGN KEY (`conference_id`) REFERENCES `elms`.`videoconference`(`id`) ON UPDATE CASCADE
) ENGINE = InnoDB;

-- --------------------------
-- End of video conferencing |
-- __________________________|

-- Parse user roles into the users table
INSERT INTO `elms`.`roles` (`name`) VALUES ("admin"), ("instructor"), ("student");

-- Parse the admin into the users table
INSERT INTO `elms`.`users` (`lname`,`oname`,`username`, `user_role`) VALUES ("Admin", "System", "admin", 1);
INSERT INTO `elms`.`userlogin` (`user_id`,`username`,`password`) VALUES (1, "admin", "$2y$10$MLr5NVbwnIhZMKCGz7L6Bu/NL9bx.P7m4g44bcTmNTFXkEmpSJtpO");
