-- SQL Server Scripts for Gcash Reconciliation Tables
-- Run these queries in your SQL Server Management Studio

-- 1. Create tblGcashReconciliation table
CREATE TABLE [dbo].[tblGcashReconciliation] (
    [ReconciliationID] [int] IDENTITY(1,1) NOT NULL,
    [GcashID] [int] NOT NULL,
    [ReconciliationDate] [datetime] NOT NULL,
    [BeginningBalance] [decimal](18, 2) NOT NULL DEFAULT 0.00,
    [TotalInflows] [decimal](18, 2) NOT NULL DEFAULT 0.00,
    [TotalOutflows] [decimal](18, 2) NOT NULL DEFAULT 0.00,
    [AvailableBalance] [decimal](18, 2) NOT NULL DEFAULT 0.00,
    [Notes] [nvarchar](max) NULL,
    [DateCreated] [datetime] NOT NULL DEFAULT GETDATE(),
    [DateUpdated] [datetime] NULL,
    CONSTRAINT [PK_tblGcashReconciliation] PRIMARY KEY CLUSTERED ([ReconciliationID] ASC),
    CONSTRAINT [FK_tblGcashReconciliation_tblGcash] FOREIGN KEY ([GcashID]) REFERENCES [dbo].[tblGcash] ([GcashID])
);

-- Create indexes for better performance
CREATE NONCLUSTERED INDEX [IX_tblGcashReconciliation_GcashID] ON [dbo].[tblGcashReconciliation] ([GcashID]);
CREATE NONCLUSTERED INDEX [IX_tblGcashReconciliation_ReconciliationDate] ON [dbo].[tblGcashReconciliation] ([ReconciliationDate]);

-- 2. Create tblGcashManualTransaction table
CREATE TABLE [dbo].[tblGcashManualTransaction] (
    [ManualTransactionID] [int] IDENTITY(1,1) NOT NULL,
    [GcashID] [int] NOT NULL,
    [TransactionType] [varchar](3) NOT NULL, -- 'IN' for deposit, 'OUT' for withdrawal
    [Amount] [decimal](18, 2) NOT NULL,
    [TransactionDate] [datetime] NOT NULL,
    [ReferenceNumber] [nvarchar](100) NULL,
    [Remarks] [nvarchar](500) NOT NULL,
    [CreatedBy] [int] NULL, -- Reference to user who created the transaction
    [DateCreated] [datetime] NOT NULL DEFAULT GETDATE(),
    [DateUpdated] [datetime] NULL,
    CONSTRAINT [PK_tblGcashManualTransaction] PRIMARY KEY CLUSTERED ([ManualTransactionID] ASC),
    CONSTRAINT [FK_tblGcashManualTransaction_tblGcash] FOREIGN KEY ([GcashID]) REFERENCES [dbo].[tblGcash] ([GcashID]),
    CONSTRAINT [CK_tblGcashManualTransaction_TransactionType] CHECK ([TransactionType] IN ('IN', 'OUT')),
    CONSTRAINT [CK_tblGcashManualTransaction_Amount] CHECK ([Amount] > 0)
);

-- Create indexes for better performance
CREATE NONCLUSTERED INDEX [IX_tblGcashManualTransaction_GcashID] ON [dbo].[tblGcashManualTransaction] ([GcashID]);
CREATE NONCLUSTERED INDEX [IX_tblGcashManualTransaction_TransactionDate] ON [dbo].[tblGcashManualTransaction] ([TransactionDate]);
CREATE NONCLUSTERED INDEX [IX_tblGcashManualTransaction_TransactionType] ON [dbo].[tblGcashManualTransaction] ([TransactionType]);

-- 3. Add trigger to update DateUpdated on tblGcashReconciliation
CREATE TRIGGER [dbo].[TR_tblGcashReconciliation_UpdateDateUpdated]
ON [dbo].[tblGcashReconciliation]
AFTER UPDATE
AS
BEGIN
    SET NOCOUNT ON;
    UPDATE [dbo].[tblGcashReconciliation]
    SET [DateUpdated] = GETDATE()
    FROM [dbo].[tblGcashReconciliation] t
    INNER JOIN inserted i ON t.[ReconciliationID] = i.[ReconciliationID];
END;

-- 4. Add trigger to update DateUpdated on tblGcashManualTransaction
CREATE TRIGGER [dbo].[TR_tblGcashManualTransaction_UpdateDateUpdated]
ON [dbo].[tblGcashManualTransaction]
AFTER UPDATE
AS
BEGIN
    SET NOCOUNT ON;
    UPDATE [dbo].[tblGcashManualTransaction]
    SET [DateUpdated] = GETDATE()
    FROM [dbo].[tblGcashManualTransaction] t
    INNER JOIN inserted i ON t.[ManualTransactionID] = i.[ManualTransactionID];
END;

-- 5. Optional: Add gcash_id column to tblPayment table if payments can be made through Gcash
-- (Uncomment the lines below if you want to track payments made through Gcash)
/*
ALTER TABLE [dbo].[tblPayment] 
ADD [gcash_id] [int] NULL;

ALTER TABLE [dbo].[tblPayment]
ADD CONSTRAINT [FK_tblPayment_tblGcash] 
FOREIGN KEY ([gcash_id]) REFERENCES [dbo].[tblGcash] ([GcashID]);

CREATE NONCLUSTERED INDEX [IX_tblPayment_gcash_id] ON [dbo].[tblPayment] ([gcash_id]);
*/

-- 6. Grant necessary permissions (adjust based on your user roles)
-- GRANT SELECT, INSERT, UPDATE, DELETE ON [dbo].[tblGcashReconciliation] TO [your_app_user];
-- GRANT SELECT, INSERT, UPDATE, DELETE ON [dbo].[tblGcashManualTransaction] TO [your_app_user];

PRINT 'Gcash Reconciliation tables created successfully!';
PRINT 'Tables created:';
PRINT '1. tblGcashReconciliation';
PRINT '2. tblGcashManualTransaction';
PRINT 'Triggers created for automatic DateUpdated updates';
PRINT 'Indexes created for better performance';