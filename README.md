# FormMaker System

A powerful and flexible form builder system built with PHP 7.0.33 and MySQL. Create custom forms, collect submissions, and export data with ease.

## Features

✅ **Form Builder Interface**
- Create and edit forms with an intuitive interface
- Add unlimited custom fields
- Real-time form preview

✅ **Field Types**
- Text Input
- Email
- Number
- Textarea
- Dropdown Select
- Radio Buttons
- Checkboxes
- Date Picker
- Telephone
- URL

✅ **Field Configuration**
- Mark fields as required
- Set default values
- Custom validation patterns (regex)
- Placeholder text
- Help text for users
- Options for select/radio/checkbox fields

✅ **Form Management**
- Create multiple forms
- Edit existing forms
- Delete forms
- Activate/deactivate forms
- View submission statistics

✅ **Data Collection**
- Secure form submission handling
- Client and server-side validation
- IP address and user agent tracking
- Timestamp for all submissions

✅ **Data Viewing & Export**
- View all submissions in a table
- Export data to CSV format
- Export data to JSON format
- Delete individual submissions

## Installation

### Requirements
- PHP 7.0.33 or higher
- MySQL 5.6 or higher
- Web server (Apache/Nginx)

### Setup Instructions

1. **Clone or download the repository**
   ```bash
   cd /var/www/html
   git clone <repository-url> formmaker
   ```

2. **Create the database**
   ```bash
   mysql -u root -p < database.sql
   ```

3. **Configure database connection**
   Edit `config.php` and update these settings:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'formmaker');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

4. **Set proper permissions**
   ```bash
   chmod 755 /var/www/html/formmaker
   chmod 644 /var/www/html/formmaker/*.php
   ```

5. **Access the application**
   Open your browser and navigate to:
   ```
   http://localhost/formmaker/
   ```

## File Structure

```
FormMaker/
├── config.php                  # Configuration file
├── index.php                   # Dashboard (main page)
├── form-builder.php            # Create/edit forms
├── form-display.php            # Display forms to users
├── view-data.php               # View submissions
├── export.php                  # Export data (CSV/JSON)
├── database.sql                # Database schema
├── includes/
│   ├── Database.php            # Database connection class
│   ├── Form.php                # Form model
│   ├── FormField.php           # FormField model
│   └── FormSubmission.php      # FormSubmission model
├── assets/
│   └── style.css               # Stylesheet
└── README.md                   # This file
```

## Usage Guide

### Creating a Form

1. Go to the dashboard (index.php)
2. Click "Create New Form"
3. Enter form name and description
4. Click "Create Form"

### Adding Fields

1. After creating a form, scroll to "Add New Field"
2. Fill in field details:
   - **Field Name**: Internal name (no spaces)
   - **Field Label**: Display label for users
   - **Field Type**: Choose from available types
   - **Required**: Check if field is mandatory
   - **Options**: For select/radio/checkbox (comma-separated)
   - **Placeholder**: Hint text
   - **Default Value**: Pre-filled value
   - **Validation Pattern**: Regex pattern for validation
   - **Help Text**: Additional guidance
3. Click "Add Field"

### Validation Patterns Examples

- **10-digit phone**: `^[0-9]{10}$`
- **Zip code**: `^[0-9]{5}$`
- **Letters only**: `^[a-zA-Z]+$`
- **Alphanumeric**: `^[a-zA-Z0-9]+$`

### Viewing Submissions

1. From dashboard, click "View Data" on any form
2. See all submissions in a table format
3. Click "Export CSV" or "Export JSON" to download data

### Sharing Forms

Share the form URL with users:
```
http://yourdomain.com/formmaker/form-display.php?form_id=1
```

## Database Schema

### Tables

**forms**
- Stores form definitions
- Fields: id, name, description, status, created_at, updated_at

**form_fields**
- Stores field configurations
- Fields: id, form_id, field_name, field_label, field_type, field_options, is_required, default_value, validation_pattern, placeholder, help_text, field_order

**form_submissions**
- Stores submission metadata
- Fields: id, form_id, submitted_at, ip_address, user_agent

**submission_data**
- Stores actual field values
- Fields: id, submission_id, field_id, field_value

## API Reference

### Form Class Methods

- `getAllForms()` - Get all forms
- `getFormById($id)` - Get form by ID
- `createForm($name, $description, $status)` - Create new form
- `updateForm($id, $name, $description, $status)` - Update form
- `deleteForm($id)` - Delete form
- `getFormWithFields($id)` - Get form with all fields
- `getSubmissionsCount($formId)` - Get submission count

### FormField Class Methods

- `getFieldsByFormId($formId)` - Get all fields for a form
- `getFieldById($id)` - Get field by ID
- `createField($data)` - Create new field
- `updateField($id, $data)` - Update field
- `deleteField($id)` - Delete field
- `getAvailableFieldTypes()` - Get available field types

### FormSubmission Class Methods

- `submitForm($formId, $fieldData)` - Submit form data
- `getFormSubmissions($formId, $limit, $offset)` - Get submissions
- `getSubmissionById($id)` - Get submission by ID
- `getFormSubmissionsWithData($formId)` - Get submissions with data
- `deleteSubmission($id)` - Delete submission
- `validateField($field, $value)` - Validate field value

## Security Features

- PDO with prepared statements (SQL injection protection)
- Input validation and sanitization
- CSRF protection ready
- XSS protection with htmlspecialchars()
- Secure password handling ready
- IP address logging

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers

## Troubleshooting

### Database Connection Error
- Check database credentials in config.php
- Verify MySQL service is running
- Ensure database exists

### Forms Not Displaying
- Check that form status is "active"
- Verify form_id in URL is correct
- Check browser console for errors

### Submission Fails
- Verify all required fields are filled
- Check validation patterns
- Review PHP error logs

## Future Enhancements

- User authentication and permissions
- Form templates
- Conditional logic (show/hide fields)
- File upload support
- Email notifications
- Form analytics and charts
- Multi-language support
- API endpoints for integration

## License

This project is open source and available for use and modification.

## Support

For issues and questions, please check the documentation or contact the development team.

## Version

**Version:** 1.0.0
**PHP Version:** 7.0.33
**Database:** MySQL
**Last Updated:** 2025
