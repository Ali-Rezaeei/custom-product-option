# Custom Product Options (Enhanced)

A WordPress + WooCommerce plugin that allows you to add **custom selectable options** to products.  
These options can be **images** or **colors**, and customers can select them before adding a product to their cart.  
Options are displayed inside a **modal with tabs**, and chosen values are stored with the order.

---

## Features
- Create custom option fields for WooCommerce products.
- Supports grouping options with **tabs**.
- Two types of values supported:
  - Color (HEX code)
  - Image (thumbnail)
- Live preview of the selected option on the product page.
- Stores chosen options in the WooCommerce order.
- **Image zoom/preview** feature in the frontend — click to view larger images.
- Simple admin interface via product meta box.
## How to Use
1. Edit (or create) a product.
2. Locate the **Custom Options (Modal + Tabs)** meta box.
3. Add one or more **fields** (these will create option groups).
4. For each field, create one or more **groups (tabs)**.
5. For each group, add values as either images or HEX color codes.
- **Note:** A single value should only have *either* an image or a color, not both.
6. Save/update the product.

---

## Frontend Behavior
- Customers click the field title/button to open the modal.
- Tabs and their respective options are displayed.
- Images can be clicked to open a fullscreen preview.
- Colors are shown as swatches (no zoom function).
- The customer’s choice is reflected in the preview area and stored in the order.
## File Structure

custom-product-options/

├── assets/

│ ├── css/

│ │ ├── admin.css # Admin panel styles

│ │ └── frontend.css # Frontend styles

│ └── js/

│ ├── admin.js # Admin panel scripts

│ └── frontend.js # Frontend scripts (modal, selection, image zoom)

├── includes/

│ ├── class-admin.php # Admin panel logic

│ ├── class-frontend.php # Frontend logic

│ └── class-order.php # Order data handling

├── custom-product-options.php # Main plugin bootstrap file

## Requirements
- WordPress 5.0+
- WooCommerce active
- PHP 7.4+