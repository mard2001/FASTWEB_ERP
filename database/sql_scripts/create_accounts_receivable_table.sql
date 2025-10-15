-- SQL Server Table Creation Script for Accounts Receivable
-- Execute this script in your SQL Server Management Studio

-- Create the Accounts Receivable table
CREATE TABLE [dbo].[tblAccountsReceivable] (
    [id] INT IDENTITY(1,1) PRIMARY KEY,
    [date] DATE NOT NULL,
    [customer_code] NVARCHAR(50) NOT NULL,
    [customer_name] NVARCHAR(255) NOT NULL,
    [so_number] NVARCHAR(50) NULL,
    [reference_number] NVARCHAR(100) NULL,
    [total_amount] DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    [terms] NVARCHAR(50) NULL,
    [status] NVARCHAR(20) NOT NULL DEFAULT 'Outstanding',
    [remarks] NTEXT NULL,
    [process_by] NVARCHAR(100) NULL,
    [payment_type] NVARCHAR(50) NULL,
    [payment_amount] DECIMAL(18,2) NULL DEFAULT 0.00,
    [payment_date] DATE NULL,
    [payment_remarks] NTEXT NULL,
    [current_balance] DECIMAL(18,2) NULL DEFAULT 0.00,
    [credit_generated] DECIMAL(18,2) NULL DEFAULT 0.00,
    [credit_received] DECIMAL(18,2) NULL DEFAULT 0.00,
    [last_balance_update] DATETIME2 NULL,
    [created_at] DATETIME2 NOT NULL DEFAULT GETDATE(),
    [updated_at] DATETIME2 NOT NULL DEFAULT GETDATE()
);

-- Add indexes for better performance
CREATE INDEX IX_tblAccountsReceivable_customer_code ON [dbo].[tblAccountsReceivable] ([customer_code]);
CREATE INDEX IX_tblAccountsReceivable_so_number ON [dbo].[tblAccountsReceivable] ([so_number]);
CREATE INDEX IX_tblAccountsReceivable_status ON [dbo].[tblAccountsReceivable] ([status]);
CREATE INDEX IX_tblAccountsReceivable_date ON [dbo].[tblAccountsReceivable] ([date]);
CREATE INDEX IX_tblAccountsReceivable_reference_number ON [dbo].[tblAccountsReceivable] ([reference_number]);

-- Add check constraints
ALTER TABLE [dbo].[tblAccountsReceivable] 
ADD CONSTRAINT CHK_tblAccountsReceivable_status 
CHECK ([status] IN ('Outstanding', 'Partial', 'Settled', 'Credit'));

ALTER TABLE [dbo].[tblAccountsReceivable] 
ADD CONSTRAINT CHK_tblAccountsReceivable_total_amount 
CHECK ([total_amount] >= 0);

ALTER TABLE [dbo].[tblAccountsReceivable] 
ADD CONSTRAINT CHK_tblAccountsReceivable_payment_amount 
CHECK ([payment_amount] >= 0 OR [payment_amount] IS NULL);

-- Add trigger to update the updated_at column automatically
CREATE TRIGGER trg_tblAccountsReceivable_UpdatedAt
ON [dbo].[tblAccountsReceivable]
AFTER UPDATE
AS
BEGIN
    SET NOCOUNT ON;
    
    UPDATE [dbo].[tblAccountsReceivable]
    SET [updated_at] = GETDATE()
    FROM [dbo].[tblAccountsReceivable] ar
    INNER JOIN inserted i ON ar.id = i.id;
END;

-- Optional: Create a view for easier reporting
CREATE VIEW [dbo].[vw_AccountsReceivableStatus] AS
SELECT 
    ar.*,
    CASE 
        WHEN ar.status = 'Outstanding' AND DATEADD(day, CAST(ISNULL(SUBSTRING(ar.terms, PATINDEX('%[0-9]%', ar.terms), PATINDEX('%[^0-9]%', SUBSTRING(ar.terms, PATINDEX('%[0-9]%', ar.terms), LEN(ar.terms)))-1), '30') AS INT), ar.date) < GETDATE() 
        THEN 1 
        ELSE 0 
    END as is_overdue,
    (ar.total_amount - ISNULL(ar.payment_amount, 0)) as balance_amount,
    DATEADD(day, CAST(ISNULL(SUBSTRING(ar.terms, PATINDEX('%[0-9]%', ar.terms), PATINDEX('%[^0-9]%', SUBSTRING(ar.terms, PATINDEX('%[0-9]%', ar.terms), LEN(ar.terms)))-1), '30') AS INT), ar.date) as due_date
FROM [dbo].[tblAccountsReceivable] ar;

-- Grant permissions (adjust as needed based on your application user)
-- GRANT SELECT, INSERT, UPDATE, DELETE ON [dbo].[tblAccountsReceivable] TO [your_application_user];
-- GRANT SELECT ON [dbo].[vw_AccountsReceivableStatus] TO [your_application_user];

PRINT 'Accounts Receivable table and related objects created successfully!';

-- Sample data insertion (optional - remove if not needed)
/*
INSERT INTO [dbo].[tblAccountsReceivable] 
([date], [customer_code], [customer_name], [so_number], [reference_number], [total_amount], [terms], [status], [remarks], [process_by])
VALUES
('2025-10-01', 'CUST001', 'Sample Customer 1', 'SO-2025-001', 'INV-001', 15000.00, '30 Days', 'Outstanding', 'Sample invoice for completed SO', 'System'),
('2025-10-05', 'CUST002', 'Sample Customer 2', 'SO-2025-002', 'INV-002', 25000.00, '15 Days', 'Partial', 'Partial payment received', 'System'),
('2025-09-20', 'CUST001', 'Sample Customer 1', 'SO-2025-003', 'INV-003', 8500.00, '30 Days', 'Settled', 'Fully paid invoice', 'System');
*/