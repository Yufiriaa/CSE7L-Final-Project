-- ============================================================================
-- UM Study Group Formation System - Database Setup
-- Database: UMStudyGroupDB
-- ============================================================================

-- Drop existing database if it exists
DROP DATABASE IF EXISTS UMStudyGroupDB;

-- Create the database
CREATE DATABASE UMStudyGroupDB;
USE UMStudyGroupDB;

-- ============================================================================
-- TABLES
-- ============================================================================

-- Student Table
CREATE TABLE Student (
    StudentID INT PRIMARY KEY AUTO_INCREMENT,
    FullName VARCHAR(100) NOT NULL,
    Email VARCHAR(100) UNIQUE NOT NULL,
    College VARCHAR(10),
    Program VARCHAR(150) NOT NULL,
    YearLevel INT NOT NULL,
    Phone VARCHAR(20),
    Password VARCHAR(255) NOT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- StudyPreference Table
CREATE TABLE StudyPreference (
    PreferenceID INT PRIMARY KEY AUTO_INCREMENT,
    StudentID INT NOT NULL UNIQUE,
    PreferredStudyTime VARCHAR(50),
    PreferredGroupSize VARCHAR(20),
    StudyMode VARCHAR(20),
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (StudentID) REFERENCES Student(StudentID) ON DELETE CASCADE
);

-- Course Table
CREATE TABLE Course (
    CourseID INT PRIMARY KEY AUTO_INCREMENT,
    CourseCode VARCHAR(20) NOT NULL UNIQUE,
    CourseTitle VARCHAR(100) NOT NULL,
    Units INT NOT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- StudyGroup Table
CREATE TABLE StudyGroup (
    GroupID INT PRIMARY KEY AUTO_INCREMENT,
    CourseID INT NOT NULL,
    GroupName VARCHAR(100) NOT NULL,
    CreatedDate DATE NOT NULL,
    MaxMembers INT NOT NULL DEFAULT 6,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (CourseID) REFERENCES Course(CourseID) ON DELETE CASCADE
);

-- GroupMembership Table
CREATE TABLE GroupMembership (
    MembershipID INT PRIMARY KEY AUTO_INCREMENT,
    StudentID INT NOT NULL,
    GroupID INT NOT NULL,
    Role VARCHAR(20) NOT NULL DEFAULT 'Member',
    DateJoined DATE NOT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_membership (StudentID, GroupID),
    FOREIGN KEY (StudentID) REFERENCES Student(StudentID) ON DELETE CASCADE,
    FOREIGN KEY (GroupID) REFERENCES StudyGroup(GroupID) ON DELETE CASCADE
);

-- Schedule Table
CREATE TABLE Schedule (
    ScheduleID INT PRIMARY KEY AUTO_INCREMENT,
    GroupID INT NOT NULL,
    StudyDate DATE NOT NULL,
    StartTime TIME NOT NULL,
    EndTime TIME NOT NULL,
    Mode VARCHAR(20) NOT NULL DEFAULT 'Online',
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (GroupID) REFERENCES StudyGroup(GroupID) ON DELETE CASCADE
);

-- AuditLog Table
CREATE TABLE AuditLog (
    LogID INT PRIMARY KEY AUTO_INCREMENT,
    ActionType VARCHAR(50) NOT NULL,
    TableAffected VARCHAR(50) NOT NULL,
    RecordID INT,
    UserID INT,
    ActionDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    Details TEXT
);

-- ============================================================================
-- INSERT VALUES IN COURSE TABLE
-- ============================================================================

INSERT INTO Course (CourseCode, CourseTitle, Units) VALUES
('NSTP 1', 'NATIONAL SERVICE TRAINING PROGRAM 1', 3),
('CS 8', 'SOCIAL ISSUES AND PROFESSIONAL PRACTICE', 3),
('GE 15', 'ENVIRONMENTAL SCIENCE', 3),
('PAHF 1', 'MOVEMENT COMPETENCY TRAINING', 2),
('CCE 109', 'FUNDAMENTALS OF PROGRAMMING', 3),
('GE 3', 'THE CONTEMPORARY WORLD', 3),
('CCE 101', 'INTRODUCTION TO COMPUTING', 3),
('GE 2', 'PURPOSIVE COMMUNICATION W/ INTERACTIVE LEARNING', 6),
('UGE 1', 'READING COMPREHENSION', 6),
('GE 4', 'MATHEMATICS IN THE MODERN WORLD', 3),
('CS 25', 'DISCRETE STRUCTURES 1', 3),
('PAHF 2', 'EXERCISE-BASED FITNESS ACTIVITIES', 2),
('GE 1', 'UNDERSTANDING THE SELF', 3),
('CCE 107', 'INTERMEDIATE PROGRAMMING', 3),
('MTH 101', 'DIFFERENTIAL CALCULUS', 3),
('NSTP 2', 'NATIONAL SERVICE TRAINING PROGRAM 2', 3),
('HCI 101', 'HUMAN COMPUTER INTERACTION', 3),
('MTH 105', 'INTEGRAL CALCULUS', 3),
('MTH 103', 'PROBABILITIES AND STATISTICS', 3),
('CST 4', 'CS PROFESSIONAL TRACK 1', 3),
('CCE 105', 'DATA STRUCTURES AND ALGORITHMS', 3),
('CS 3', 'DISCRETE STRUCTURES 2', 3),
('CS 26', 'SOFTWARE DEVELOPMENT FUNDAMENTALS', 3),
('CCE 104', 'INFORMATION MANAGEMENT', 3),
('PAHF 3', 'DANCE AND SPORTS 1', 2),
('GE 6', 'RIZAL''S LIFE AND WORKS', 3),
('PAHF 4', 'DANCE AND SPORTS 2', 2),
('CSE 7', 'CS PROFESSIONAL ELECTIVE 1', 3),
('BSM 312', 'DIFFERENTIAL EQUATIONS', 3),
('BSM 222', 'LINEAR ALGEBRA', 3),
('GE 11', 'THE ENTREPRENEURIAL MIND', 3),
('CST 5', 'CS PROFESSIONAL TRACK 2', 3),
('GE 8', 'READINGS IN PHILIPPINE HISTORY', 3),
('CS 6', 'ALGORITHMS AND COMPLEXITY', 3),
('PHYS 101', 'COLLEGE PHYSICS 1', 4),
('CS 12', 'SOFTWARE ENGINEERING 1', 3),
('GE 5', 'SCIENCE, TECHNOLOGY AND SOCIETY', 3),
('GE 7', 'ART APPRECIATION', 3),
('CS 11', 'ARCHITECTURE AND ORGANIZATION', 3),
('BSM 325', 'NUMERICAL ANALYSIS', 3),
('CS 15', 'PROGRAMMING LANGUAGES', 3),
('CST 9', 'CS PROFESSIONAL TRACK 3', 3),
('CSE 10', 'CS PROFESSIONAL ELECTIVE 2', 3),
('GE 20', 'READING VISUAL ARTS', 3),
('CS 17', 'SOFTWARE ENGINEERING 2', 3),
('CSE 13', 'CS PROFESSIONAL ELECTIVE 3', 3),
('PHYS 102', 'COLLEGE PHYSICS 2', 4),
('CST 14', 'CS PROFESSIONAL TRACK 4', 3),
('UGE 2', 'TECHNICAL WRITING IN THE DISCIPLINE', 3),
('CS 20', 'CS PROFESSIONAL TRACK 5', 3),
('CS 24', 'CS PROFESSIONAL TRACK 6', 3),
('CS 18', 'CS THESIS WRITING 1', 3),
('CS 21', 'NETWORKS AND COMMUNICATIONS', 3),
('CS 19', 'OPERATING SYSTEMS', 4),
('CCE 106', 'APPLICATIONS DEV''T AND EMERGING TECHNOLOGIES', 3)
ON DUPLICATE KEY UPDATE CourseTitle=VALUES(CourseTitle), Units=VALUES(Units);


-- ============================================================================
-- INDEXES FOR PERFORMANCE
-- ============================================================================

CREATE INDEX idx_student_email ON Student(Email);
CREATE INDEX idx_studypref_student ON StudyPreference(StudentID);
CREATE INDEX idx_course_code ON Course(CourseCode);
CREATE INDEX idx_group_course ON StudyGroup(CourseID);
CREATE INDEX idx_membership_student ON GroupMembership(StudentID);
CREATE INDEX idx_membership_group ON GroupMembership(GroupID);
CREATE INDEX idx_schedule_group ON Schedule(GroupID);
CREATE INDEX idx_audit_user ON AuditLog(UserID);
CREATE INDEX idx_audit_action ON AuditLog(ActionType);

-- ============================================================================
-- TRIGGERS
-- ============================================================================

-- Trigger 1: Prevent over-capacity when adding members to a study group
DELIMITER //

CREATE TRIGGER trg_Before_Insert_GroupMembership
BEFORE INSERT ON GroupMembership
FOR EACH ROW
BEGIN
    DECLARE current_members INT;
    DECLARE max_members INT;

    -- Count current number of members in the group
    SELECT COUNT(*) INTO current_members
    FROM GroupMembership
    WHERE GroupID = NEW.GroupID;

    -- Get the maximum allowed members for the group
    SELECT MaxMembers INTO max_members
    FROM StudyGroup
    WHERE GroupID = NEW.GroupID;

    -- Check if adding this member would exceed the limit
    IF current_members >= max_members THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Cannot join group: Maximum number of members reached.';
    END IF;
END //

DELIMITER ;

-- Trigger 2: Audit log trigger for group membership inserts
DELIMITER //

CREATE TRIGGER trg_After_Insert_GroupMembership
AFTER INSERT ON GroupMembership
FOR EACH ROW
BEGIN
    INSERT INTO AuditLog (ActionType, TableAffected, RecordID, UserID, Details)
    VALUES ('JOIN_GROUP', 'GroupMembership', NEW.MembershipID, NEW.StudentID, 
            CONCAT('Student ', NEW.StudentID, ' joined StudyGroup ', NEW.GroupID, ' as ', NEW.Role));
END //

DELIMITER ;

-- Trigger 3: Audit log trigger for group membership deletes
DELIMITER //

CREATE TRIGGER trg_After_Delete_GroupMembership
AFTER DELETE ON GroupMembership
FOR EACH ROW
BEGIN
    INSERT INTO AuditLog (ActionType, TableAffected, RecordID, UserID, Details)
    VALUES ('LEAVE_GROUP', 'GroupMembership', OLD.MembershipID, OLD.StudentID, 
            CONCAT('Student ', OLD.StudentID, ' left StudyGroup ', OLD.GroupID));
END //

DELIMITER ;

-- Trigger 4: Audit log trigger for study group creation
DELIMITER //

CREATE TRIGGER trg_After_Insert_StudyGroup
AFTER INSERT ON StudyGroup
FOR EACH ROW
BEGIN
    INSERT INTO AuditLog (ActionType, TableAffected, RecordID, Details)
    VALUES ('CREATE_GROUP', 'StudyGroup', NEW.GroupID, 
            CONCAT('Study Group "', NEW.GroupName, '" created for CourseID ', NEW.CourseID));
END //

DELIMITER ;

-- Trigger 5: Audit log trigger for study group deletion
DELIMITER //

CREATE TRIGGER trg_After_Delete_StudyGroup
AFTER DELETE ON StudyGroup
FOR EACH ROW
BEGIN
    INSERT INTO AuditLog (ActionType, TableAffected, RecordID, Details)
    VALUES ('DELETE_GROUP', 'StudyGroup', OLD.GroupID, 
            CONCAT('Study Group "', OLD.GroupName, '" (ID:', OLD.GroupID, ') was deleted'));
END //

DELIMITER ;

-- Trigger 6: Audit log trigger for schedule creation
DELIMITER //

CREATE TRIGGER trg_After_Insert_Schedule
AFTER INSERT ON Schedule
FOR EACH ROW
BEGIN
    INSERT INTO AuditLog (ActionType, TableAffected, RecordID, Details)
    VALUES ('CREATE_SCHEDULE', 'Schedule', NEW.ScheduleID, 
            CONCAT('Schedule created for GroupID ', NEW.GroupID, ' on ', NEW.StudyDate, 
                   ' from ', NEW.StartTime, ' to ', NEW.EndTime));
END //

DELIMITER ;

-- Trigger 7: Audit log trigger for course creation
DELIMITER //

CREATE TRIGGER trg_After_Insert_Course
AFTER INSERT ON Course
FOR EACH ROW
BEGIN
    INSERT INTO AuditLog (ActionType, TableAffected, RecordID, Details)
    VALUES ('CREATE_COURSE', 'Course', NEW.CourseID, 
            CONCAT('Course "', NEW.CourseCode, ' - ', NEW.CourseTitle, '" created'));
END //

DELIMITER ;

-- ============================================================================
-- STORED PROCEDURES: LOCKING & CONCURRENCY CONTROL
-- ============================================================================

-- Stored Procedure 1: Safe Join Group with FOR UPDATE Locking
-- Implements: Transaction Locking + Concurrency Control
-- Uses SELECT FOR UPDATE to prevent race conditions when checking capacity
DELIMITER //

CREATE PROCEDURE sp_JoinGroupSafely(
    IN p_StudentID INT,
    IN p_GroupID INT,
    OUT p_Success BOOLEAN,
    OUT p_Message VARCHAR(255)
)
BEGIN
    DECLARE current_count INT;
    DECLARE max_size INT;
    DECLARE member_exists INT;
    
    -- Default values
    SET p_Success = FALSE;
    SET p_Message = 'Unknown error';
    
    START TRANSACTION;
    
    BEGIN
        -- Step 1: Lock the group and safely count current members
        SELECT COUNT(*) INTO current_count
        FROM GroupMembership 
        WHERE GroupID = p_GroupID 
        FOR UPDATE;  -- Lock the group to prevent concurrent modifications
        
        -- Step 2: Lock and get max members
        SELECT MaxMembers INTO max_size
        FROM StudyGroup 
        WHERE GroupID = p_GroupID 
        FOR UPDATE;  -- Lock the StudyGroup row
        
        -- Step 3: Check if student is already a member
        SELECT COUNT(*) INTO member_exists
        FROM GroupMembership
        WHERE StudentID = p_StudentID AND GroupID = p_GroupID;
        
        IF member_exists > 0 THEN
            SET p_Message = 'ERROR: You are already a member of this group';
            ROLLBACK;
        ELSEIF current_count >= max_size THEN
            SET p_Message = 'ERROR: Group is full. Cannot join.';
            ROLLBACK;
        ELSE
            -- Step 4: Safe to insert - all locks held during this check
            INSERT INTO GroupMembership (StudentID, GroupID, Role, DateJoined)
            VALUES (p_StudentID, p_GroupID, 'Member', CURDATE());
            
            -- Step 5: Log the transaction
            INSERT INTO AuditLog (ActionType, TableAffected, RecordID, UserID, Details)
            VALUES ('JOIN_GROUP', 'GroupMembership', LAST_INSERT_ID(), p_StudentID, 
                    CONCAT('Student ', p_StudentID, ' successfully joined GroupID ', p_GroupID, ' (', current_count + 1, '/', max_size, ')'));
            
            SET p_Success = TRUE;
            SET p_Message = 'SUCCESS: Joined group successfully';
            COMMIT;  -- All changes saved atomically
        END IF;
    END;
END //

DELIMITER ;

-- Stored Procedure 2: Atomic Schedule Creation with Validation
-- Implements: Transaction Logging + Concurrency Control
-- Uses atomic transaction to ensure schedule and audit log are both created or both rolled back
DELIMITER //

CREATE PROCEDURE sp_CreateScheduleAtomic(
    IN p_GroupID INT,
    IN p_StudyDate DATE,
    IN p_StartTime TIME,
    IN p_EndTime TIME,
    IN p_StudyMode VARCHAR(20),
    OUT p_Success BOOLEAN,
    OUT p_Message VARCHAR(255),
    OUT p_ScheduleID INT
)
BEGIN
    DECLARE group_exists INT;
    DECLARE member_count INT;
    
    SET p_Success = FALSE;
    SET p_Message = 'Unknown error';
    SET p_ScheduleID = NULL;
    
    START TRANSACTION;
    
    BEGIN
        -- Step 1: Lock group and validate it exists
        SELECT COUNT(*) INTO group_exists
        FROM StudyGroup
        WHERE GroupID = p_GroupID
        FOR UPDATE;
        
        IF group_exists = 0 THEN
            SET p_Message = 'ERROR: Study group does not exist';
            ROLLBACK;
        ELSEIF p_StartTime >= p_EndTime THEN
            SET p_Message = 'ERROR: Start time must be before end time';
            ROLLBACK;
        ELSE
            -- Step 2: Insert schedule (will trigger audit log via trigger)
            INSERT INTO Schedule (GroupID, StudyDate, StartTime, EndTime, StudyMode)
            VALUES (p_GroupID, p_StudyDate, p_StartTime, p_EndTime, p_StudyMode);
            
            SET p_ScheduleID = LAST_INSERT_ID();
            
            -- Step 3: Get member count for logging
            SELECT COUNT(*) INTO member_count
            FROM GroupMembership
            WHERE GroupID = p_GroupID;
            
            -- Step 4: Additional audit log (redundant but explicit)
            INSERT INTO AuditLog (ActionType, TableAffected, RecordID, Details)
            VALUES ('CREATE_SCHEDULE_VALIDATED', 'Schedule', p_ScheduleID,
                    CONCAT('Schedule created: GroupID=', p_GroupID, ', MemberCount=', member_count, 
                           ', Date=', p_StudyDate, ', Time=', p_StartTime, '-', p_EndTime));
            
            SET p_Success = TRUE;
            SET p_Message = 'SUCCESS: Schedule created and logged';
            COMMIT;  -- All operations succeed together
        END IF;
    END;
END //

DELIMITER ;

-- ============================================================================
-- VIEWS FOR COMMON QUERIES
-- ============================================================================

-- View: Group Members with Student Details
CREATE VIEW vw_GroupMembers AS
SELECT 
    gm.MembershipID,
    gm.StudentID,
    gm.GroupID,
    gm.Role,
    gm.DateJoined,
    s.FullName,
    s.Email,
    s.Program,
    s.YearLevel
FROM GroupMembership gm
INNER JOIN Student s ON gm.StudentID = s.StudentID;

-- View: Group Details with Course Info and Member Count
CREATE VIEW vw_GroupDetails AS
SELECT 
    sg.GroupID,
    sg.GroupName,
    sg.CourseID,
    c.CourseCode,
    c.CourseTitle,
    sg.CreatedDate,
    sg.MaxMembers,
    COUNT(gm.MembershipID) AS CurrentMembers
FROM StudyGroup sg
INNER JOIN Course c ON sg.CourseID = c.CourseID
LEFT JOIN GroupMembership gm ON sg.GroupID = gm.GroupID
GROUP BY sg.GroupID, sg.GroupName, sg.CourseID, c.CourseCode, c.CourseTitle, sg.CreatedDate, sg.MaxMembers;

-- View: Student Study Groups
CREATE VIEW vw_StudentGroups AS
SELECT 
    s.StudentID,
    s.FullName,
    sg.GroupID,
    sg.GroupName,
    c.CourseCode,
    c.CourseTitle,
    gm.DateJoined,
    gm.Role
FROM Student s
INNER JOIN GroupMembership gm ON s.StudentID = gm.StudentID
INNER JOIN StudyGroup sg ON gm.GroupID = sg.GroupID
INNER JOIN Course c ON sg.CourseID = c.CourseID;

-- ============================================================================
-- DATABASE SETUP COMPLETE
-- Ready for use. All tables are empty by default.
-- ============================================================================
