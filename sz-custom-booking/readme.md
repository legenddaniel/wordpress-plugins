    Plugin Name: Custom Booking
    Version: 1.0.0
    Plugin URI: null
    Description: Custom booking
    Author: Siyuan Zuo
    Author URI: https://github.com/legenddaniel
    Text Domain: custom-booking

# Browser Support: IE not supported. need Babel

# Issues:

## Database:

1. Field name is same as product name now. If product name is changed, the codes 'Add passes to database' will create a new field in the database with the new name. Then the values in the old field will not be retrieved. Also the query conditions in 'Display available passes' need to be changed in this case.