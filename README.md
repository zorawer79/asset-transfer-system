# Asset Transfer Management System

A comprehensive PHP-based application for tracking and managing asset transfers with database support for InfinityFree hosting.

## Features

✅ **Dashboard** - Real-time asset statistics and overview
✅ **Add New Asset** - Multi-step wizard with:
   - Asset Information (Name, ID, Group)
   - Identification (RFID Code, Status)
   - Transfer Details (Locations, Dates)
   - Photo Upload & Notes
   
✅ **Asset Management**
   - View asset details
   - Edit asset information
   - Delete assets
   - Search and filter
   
✅ **Transfer Tracking** - Complete transfer history
✅ **Photo Management** - Upload and view asset photos
✅ **Notes System** - Add detailed notes for each asset
✅ **Reports** - Comprehensive statistics and analytics

## Database Tables

- `asset_groups` - Asset categories
- `assets` - Main assets table
- `locations` - Storage locations
- `transfers` - Transfer history
- `asset_photos` - Asset images
- `asset_notes` - Additional notes

## Installation

1. **Extract files** to your InfinityFree hosted directory
2. **Create database** - Import `database/schema.sql`
3. **Configure** - Update `config/database.php` with your credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'asset_transfer_db');
   ```
4. **Access** - Navigate to `index.php` in your browser

## Usage

### Adding an Asset
1. Click "Add Asset" button
2. Fill in asset information
3. Add identification details
4. Enter transfer information
5. Upload photo and add notes
6. Click "Save Asset"

### Viewing Assets
- Search by name, RFID, or ID
- Filter by group or status
- Click asset name to view details
- View transfer history, photos, and notes

### Reports
- View statistics on dashboard
- Access detailed reports page
- See asset distribution by status and group

## File Structure

```
asset-transfer-system/
├── config/
│   └── database.php          # Database configuration
├── database/
│   └── schema.sql            # Database schema
├── pages/
│   ├── add_asset.php         # Add new asset form
│   ├── view_asset.php        # View asset details
│   ├── edit_asset.php        # Edit asset
│   ├── delete_asset.php      # Delete asset
│   └── reports.php           # Reports page
├── assets/
│   └── css/
│       └── style.css         # Custom styles
├── uploads/
│   └── assets/               # Asset photos
└── index.php                 # Dashboard
```

## Asset Status

- **New** - Newly added assets
- **In Store** - Assets in storage
- **Used** - Assets currently in use
- **Scrap** - Non-functional/disposed assets

## Requirements

- PHP 7.4+
- MySQL 5.7+
- Modern web browser

## Support

For issues or questions, please check the code comments or contact support.

## License

Open source - Free to use and modify
