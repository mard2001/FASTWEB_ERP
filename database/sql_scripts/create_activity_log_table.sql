-- =============================================
-- Activity Log Table Creation Script
-- For FASTWEB ERP System
-- Execute this in SQL Server Management Studio (SSMS)
-- =============================================

USE [FASTERP]
GO

-- Create Activity Log Table
IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='activity_log' AND xtype='U')
BEGIN
    CREATE TABLE [dbo].[activity_log](
        [id] [bigint] IDENTITY(1,1) NOT NULL,
        [log_name] [nvarchar](255) NULL,
        [description] [nvarchar](max) NOT NULL,
        [subject_type] [nvarchar](255) NULL,
        [event] [nvarchar](255) NULL,
        [subject_id] [bigint] NULL,
        [causer_type] [nvarchar](255) NULL,
        [causer_id] [bigint] NULL,
        [properties] [nvarchar](max) NULL,
        [batch_uuid] [uniqueidentifier] NULL,
        [created_at] [datetime2](0) NULL,
        [updated_at] [datetime2](0) NULL,
        CONSTRAINT [PK_activity_log] PRIMARY KEY CLUSTERED ([id] ASC)
    ) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
    
    PRINT 'Activity Log table created successfully'
END
ELSE
BEGIN
    PRINT 'Activity Log table already exists'
END
GO

-- Create indexes for better performance
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = 'IX_activity_log_subject' AND object_id = OBJECT_ID('activity_log'))
BEGIN
    CREATE NONCLUSTERED INDEX [IX_activity_log_subject] ON [dbo].[activity_log]
    (
        [subject_type] ASC,
        [subject_id] ASC
    ) ON [PRIMARY]
    PRINT 'Index IX_activity_log_subject created'
END

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = 'IX_activity_log_causer' AND object_id = OBJECT_ID('activity_log'))
BEGIN
    CREATE NONCLUSTERED INDEX [IX_activity_log_causer] ON [dbo].[activity_log]
    (
        [causer_type] ASC,
        [causer_id] ASC
    ) ON [PRIMARY]
    PRINT 'Index IX_activity_log_causer created'
END

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = 'IX_activity_log_log_name' AND object_id = OBJECT_ID('activity_log'))
BEGIN
    CREATE NONCLUSTERED INDEX [IX_activity_log_log_name] ON [dbo].[activity_log]
    (
        [log_name] ASC
    ) ON [PRIMARY]
    PRINT 'Index IX_activity_log_log_name created'
END

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = 'IX_activity_log_created_at' AND object_id = OBJECT_ID('activity_log'))
BEGIN
    CREATE NONCLUSTERED INDEX [IX_activity_log_created_at] ON [dbo].[activity_log]
    (
        [created_at] DESC
    ) ON [PRIMARY]
    PRINT 'Index IX_activity_log_created_at created'
END

PRINT 'Activity Log table setup completed successfully!'
GO
