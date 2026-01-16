# Excusator 3000 - User Guide

## Introduction
Excusator 3000 helps you find the perfect excuse for any situation. It features a fun spinning wheel interface to randomly select a category and generate an excuse.

## The Spinning Wheel
1.  Navigate to the **Home Page**.
2.  You will see a colorful spinning wheel.
3.  Click the **"DAME UNA EXCUSA"** button in the center.
4.  The wheel will spin and land on a random category.
5.  A random excuse from that category will be displayed in a pop-up window.

*Note: The button will be disabled if there are fewer than 5 valid categories available.*

## Administration Panel
Use the navigation links in the top right corner to access the admin area.

### Managing Categories
Navigate to **Admin Categories**.
- **View**: See a list of all categories with their status and excuse count.
- **Create**: Click "Create New", enter a name, check "Active" if you want it to be available on the wheel, and click Save.
- **Edit**: Click "Edit" next to a category to change its name or status.
- **Delete**: Click "Delete" to remove a category (this is a soft delete, data remains in DB but inactive).

### Managing Excuses
Navigate to **Admin Excuses**.
- **View**: See a list of all excuses and their associated category.
- **Create**: Click "Create New", enter the excuse text, select a category, and click Save.
- **Edit**: Click "Edit" to modify an existing excuse.
- **Delete**: Click "Delete" to remove a particular excuse.

### Requirements for the Wheel
For the spinning wheel to function correctly:
- Multiple categories must be created and set to **Active**.
- Each active category must have at least **5 excuses** created for it.
- There must be at least **5 valid categories** in total.
