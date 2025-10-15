-- Create tblAccountsReceivable table for the ERP system
USE FASTWEB_ERP;

-- Drop table if it exists (for development purposes)
IF OBJECT_ID('dbo.tblAccountsReceivable', 'U') IS NOT NULL 
DROP TABLE dbo.tblAccountsReceivable;

-- Create the table
CREATE TABLE dbo.tblAccountsReceivable (
    id int IDENTITY(1,1) PRIMARY KEY,
    date date NOT NULL,
    customer_code nvarchar(50) NOT NULL,
    customer_name nvarchar(255) NULL,
    so_number nvarchar(50) NOT NULL,
    reference_number nvarchar(100) NULL,
    total_amount decimal(18,2) NOT NULL DEFAULT 0,
    terms nvarchar(50) NULL DEFAULT '30 Days',
    status nvarchar(20) NOT NULL DEFAULT 'Outstanding',
    remarks nvarchar(500) NULL,
    process_by nvarchar(100) NULL,
    payment_type nvarchar(20) NULL,
    payment_amount decimal(18,2) NULL DEFAULT 0,
    payment_date date NULL,
    payment_remarks nvarchar(500) NULL,
    current_balance decimal(18,2) NULL,
    credit_generated decimal(18,2) NULL DEFAULT 0,
    credit_received decimal(18,2) NULL DEFAULT 0,
    last_balance_update datetime NULL,
    created_at datetime NOT NULL DEFAULT GETDATE(),
    updated_at datetime NOT NULL DEFAULT GETDATE()
);

-- Create indexes for better performance
CREATE INDEX IX_AccountsReceivable_CustomerCode ON dbo.tblAccountsReceivable(customer_code);
CREATE INDEX IX_AccountsReceivable_SONumber ON dbo.tblAccountsReceivable(so_number);
CREATE INDEX IX_AccountsReceivable_Status ON dbo.tblAccountsReceivable(status);
CREATE INDEX IX_AccountsReceivable_Date ON dbo.tblAccountsReceivable(date);

-- Create a computed column for balance
ALTER TABLE dbo.tblAccountsReceivable 
ADD balance_amount AS (total_amount - ISNULL(payment_amount, 0)) PERSISTED;

-- Add check constraints
ALTER TABLE dbo.tblAccountsReceivable 
ADD CONSTRAINT CK_AccountsReceivable_TotalAmount CHECK (total_amount >= 0);

ALTER TABLE dbo.tblAccountsReceivable 
ADD CONSTRAINT CK_AccountsReceivable_PaymentAmount CHECK (payment_amount >= 0);

ALTER TABLE dbo.tblAccountsReceivable 
ADD CONSTRAINT CK_AccountsReceivable_Status CHECK (status IN ('Outstanding', 'Settled', 'Cancelled'));

-- Create trigger to automatically update updated_at timestamp
CREATE TRIGGER TR_AccountsReceivable_UpdateTimestamp
ON dbo.tblAccountsReceivable
AFTER UPDATE
AS
BEGIN
    SET NOCOUNT ON;
    UPDATE dbo.tblAccountsReceivable 
    SET updated_at = GETDATE()
    WHERE id IN (SELECT DISTINCT id FROM inserted);
END;

-- Insert sample data for testing
INSERT INTO dbo.tblAccountsReceivable (
    date, customer_code, customer_name, so_number, total_amount, terms, status, process_by
) VALUES 
('2024-10-01', 'CUST001', 'Sample Customer 1', 'SO-2024-001', 15000.00, '30 Days', 'Outstanding', 'system'),
('2024-10-05', 'CUST002', 'Sample Customer 2', 'SO-2024-002', 25000.00, '15 Days', 'Outstanding', 'system'),
('2024-09-15', 'CUST003', 'Sample Customer 3', 'SO-2024-003', 10000.00, '30 Days', 'Settled', 'system');

-- Update the settled record with payment information
UPDATE dbo.tblAccountsReceivable 
SET payment_amount = 10000.00, payment_type = 'cash', payment_date = '2024-09-20'
WHERE so_number = 'SO-2024-003';

PRINT 'tblAccountsReceivable table created successfully with sample data.';