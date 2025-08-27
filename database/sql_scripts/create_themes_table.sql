-- Simple themes table for FASTWEB_ERP
CREATE TABLE themes (
    id BIGINT IDENTITY(1,1) PRIMARY KEY,
    name NVARCHAR(255) NOT NULL UNIQUE,
    primary_color NVARCHAR(7) NOT NULL DEFAULT '#007bff',
    secondary_color NVARCHAR(7) NOT NULL DEFAULT '#6c757d',
    accent_color NVARCHAR(7) NOT NULL DEFAULT '#28a745',
    background_color NVARCHAR(7) NOT NULL DEFAULT '#ffffff',
    text_color NVARCHAR(7) NOT NULL DEFAULT '#212529',
    heading_font NVARCHAR(100) NOT NULL DEFAULT 'Arial',
    body_font NVARCHAR(100) NOT NULL DEFAULT 'Arial',
    is_active BIT NOT NULL DEFAULT 0,
    created_at DATETIME2(0) NOT NULL DEFAULT GETDATE(),
    updated_at DATETIME2(0) NOT NULL DEFAULT GETDATE()
);

-- Insert default theme
INSERT INTO themes (
    name,
    primary_color,
    secondary_color,
    accent_color,
    background_color,
    text_color,
    heading_font,
    body_font,
    is_active
) VALUES (
    'Default Theme',
    '#007bff',
    '#6c757d',
    '#28a745',
    '#ffffff',
    '#212529',
    'Arial',
    'Arial',
    1
);