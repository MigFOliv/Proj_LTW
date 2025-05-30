# ltw13g07

## Features

**User:**
- [x] Register a new account.
- [x] Log in and out.
- [x] Edit their profile, including their name, username, password, and email.

**Freelancers:**
- [x] List new services, providing details such as category, pricing, delivery time, and service description, along with images or videos.
- [x] Track and manage their offered services.
- [x] Respond to inquiries from clients regarding their services and provide custom offers if needed.
- [x] Mark services as completed once delivered.

**Clients:**
- [x] Browse services using filters like category, price, and rating.
- [x] Engage with freelancers to ask questions or request custom orders.
- [x] Hire freelancers and proceed to checkout (simulate payment process).
- [x] Leave ratings and reviews for completed services.

**Admins:**
- [x] Elevate a user to admin status.
- [x] Introduce new service categories and other pertinent entities.
- [x] Oversee and ensure the smooth operation of the entire system.

**Extra:**
- [x] Favorite/unfavorite services.
- [x] View public profiles of freelancers.
- [x] View system statistics.
- [x] Profile picture upload and display.
- [x] CSRF protection on profile editing.
- [x] Custom default profile image fallback.
- [x] Validation for profile input fields (e.g., max length, email format, image type).
- [x] Duplicate email prevention during profile update.
- [x] Display of approved freelancer services on profile pages.
- [x] Multi-Currency support (EUR, GBP, USD and JPY).

## Running

    sqlite3 database/database.db < database/database.sql
    php -S localhost:9000

## Credentials

- admin : miguel / miguel ou cristiano / cristiano
- user : guilherme / guilherme ou ltw / ltwltw
