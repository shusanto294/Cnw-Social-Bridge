# CNW Social Bridge - Admin Guide

## Table of Contents

1. [Installation & Setup](#installation--setup)
2. [Shortcode Usage](#shortcode-usage)
3. [Admin Dashboard](#admin-dashboard)
4. [User Management](#user-management)
5. [Thread Management](#thread-management)
6. [Reply Management](#reply-management)
7. [Message Management](#message-management)
8. [Tag Management](#tag-management)
9. [Category Management](#category-management)
10. [Vote Management](#vote-management)
11. [Reputation Management](#reputation-management)
12. [Reports Management](#reports-management)
13. [Take Action (Warnings & Suspensions)](#take-action-warnings--suspensions)
14. [Guidelines Editor](#guidelines-editor)
15. [Forum Settings](#forum-settings)
16. [Pusher / Real-Time Configuration](#pusher--real-time-configuration)
17. [Logo Upload](#logo-upload)
18. [Import & Export](#import--export)
19. [Roles & Capabilities](#roles--capabilities)
20. [Database Tables](#database-tables)
21. [Troubleshooting](#troubleshooting)

---

## Installation & Setup

### Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.7 or higher

### Installation

1. Upload the `cnw-social-bridge` folder to `/wp-content/plugins/`.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. On activation, the plugin automatically:
   - Creates all required database tables.
   - Registers custom user roles: Forum Member, Moderator, and Forum Admin.
   - Sets up default options.

### Initial Configuration

After activation:

1. Navigate to **CNW Social Bridge** in the WordPress admin sidebar.
2. Configure **Pusher settings** for real-time features (messaging, notifications, online status).
3. Set **Thread and Reply default statuses** (whether new posts need approval).
4. Upload a **forum logo** (optional).
5. Write **Community Guidelines** for your users.
6. Create a WordPress page and add the `[cnw_social_bridge]` shortcode.

---

## Shortcode Usage

Add the forum to any WordPress page or post using:

```
[cnw_social_bridge]
```

This renders the full forum interface including navigation, question listing, messaging, user profiles, and all other features. The forum uses hash-based routing (`/#/thread/21`, `/#/messages`, etc.) so it works within a single WordPress page.

**Recommended:** Create a dedicated page (e.g., "Community" or "Forum") and add only this shortcode to its content.

---

## Admin Dashboard

Navigate to **CNW Social Bridge** in the WordPress admin menu to access the dashboard.

### Dashboard Overview

The dashboard displays:

- **Quick Stats** - Total counts for: Users, Threads, Tags, Categories, Votes, Replies, Reputation entries, and Reports.
- **Recent Threads** - A table of the most recently created threads with links to edit them.
- **Report Alert** - A badge shows the number of open reports requiring attention.
- **Quick Navigation Cards** - Links to all admin sections.

### Admin Menu Structure

| Menu Item | Description |
|-----------|-------------|
| Dashboard | Overview stats and quick links |
| Forum Users | Manage forum member accounts |
| Threads | Manage all forum threads/questions |
| Replies | Manage all replies/answers |
| Messages | View and manage direct messages |
| Tags | Manage topic tags |
| Categories | Manage thread categories |
| Votes | View and manage votes |
| Reputation | Manage reputation point entries |
| Guidelines | Edit community guidelines |
| Reports | View and manage user-submitted reports |
| Take Action | Issue warnings and suspensions |
| Import/Export | Backup and restore forum data |
| Settings | Configure forum and Pusher settings |

---

## User Management

Navigate to **CNW Social Bridge > Forum Users**.

### Viewing Users

The user list shows all registered forum users with:

- Avatar
- Display name
- Email
- Professional title
- Verified label
- Registration date

Use the search box to find specific users.

### Creating a User

1. Click **Add New User**.
2. Fill in the required fields:
   - **Email** (required, must be unique)
   - **Username** (required)
   - **Password** (required)
   - **First Name** / **Last Name**
   - **Professional Title** (e.g., "Licensed Clinical Social Worker")
   - **Phone Number**
   - **Verified Label** (e.g., "Verified Social Worker")
   - **Anonymous** toggle
3. Click **Create User**.

### Editing a User

1. Click **Edit** on any user row.
2. Modify any fields as needed.
3. Optionally upload or change the user's **avatar**.
4. Click **Save Changes**.

### Deleting Users

- Click **Delete** on a user row (confirmation required).
- For bulk deletion: select multiple users with checkboxes and use the **Bulk Actions** dropdown.

### Sending Password Reset

Click the **Reset Password** link on a user's edit page to send them a password reset email.

---

## Thread Management

Navigate to **CNW Social Bridge > Threads**.

### Viewing Threads

The thread list shows all questions with:

- Title
- Author
- Category
- Status (Published, Pending, Approved, Rejected)
- Reply count
- View count
- Pinned/Closed status
- Created date

### Filtering Threads

Use the status filter dropdown to view threads by status:

- **All** - All threads regardless of status.
- **Published** - Visible to all users.
- **Pending** - Awaiting moderator approval.
- **Approved** - Approved by moderator.
- **Rejected** - Rejected by moderator.

A badge next to the menu item shows the count of pending threads.

### Editing a Thread

1. Click **Edit** on any thread row.
2. You can modify:
   - **Title** and **Content**
   - **Status** (Published, Pending, Approved, Rejected)
   - **Category**
   - **Tags**
   - **Anonymous** toggle
   - **Pinned** toggle - Pins the thread to the top of the question list.
   - **Closed** toggle - Closes the thread to prevent new replies.
   - **View Count** - Manually adjust if needed.
3. Click **Save Changes**.

### Deleting Threads

- Click **Delete** on a thread row (confirmation required).
- Bulk delete: select multiple threads and use the Bulk Actions dropdown.

### Content Approval Workflow

If the default thread status is set to **Pending** in Settings:

1. New threads are created with "Pending" status.
2. They appear in the admin thread list with a "Pending" badge.
3. Edit the thread and change status to "Approved" or "Published" to make it visible.
4. Or change to "Rejected" to hide it.

---

## Reply Management

Navigate to **CNW Social Bridge > Replies**.

### Viewing Replies

The reply list shows all answers/comments with:

- Content (truncated)
- Author
- Parent thread
- Status
- Solution status
- Created date

### Filtering Replies

Filter by status: All, Pending, Approved, Rejected.

A badge shows the count of pending replies.

### Editing a Reply

1. Click **Edit** on any reply row.
2. You can modify:
   - **Content**
   - **Status** (Pending, Approved, Rejected)
   - **Solution** toggle - Mark/unmark as the best answer.
   - **Anonymous** toggle
3. Click **Save Changes**.

### Deleting Replies

- Click **Delete** on a reply row.
- Bulk delete via checkboxes and Bulk Actions.

---

## Message Management

Navigate to **CNW Social Bridge > Messages**.

### Viewing Messages

The message list shows all direct messages between users with:

- Sender name
- Recipient name
- Content (truncated)
- Attachment info
- Read status
- Sent date

### Editing a Message

Click **Edit** to modify the message content.

### Deleting Messages

- Click **Delete** on individual messages.
- Bulk delete via checkboxes.

> **Note:** Deleting messages is permanent. Users will no longer see the deleted messages in their conversations.

---

## Tag Management

Navigate to **CNW Social Bridge > Tags**.

### Viewing Tags

Each tag shows:

- Tag name
- Description
- Number of questions using the tag

### Creating a Tag

1. Click **Add New Tag**.
2. Enter the **Name** and optional **Description**.
3. Click **Create Tag**.

### Editing a Tag

Click **Edit** to modify the tag name or description.

### Deleting Tags

- Click **Delete** to remove a tag.
- Bulk delete via checkboxes.

> **Note:** Deleting a tag removes it from all associated threads but does not delete the threads themselves.

---

## Category Management

Navigate to **CNW Social Bridge > Categories**.

### Viewing Categories

Each category shows:

- Category name
- Slug
- Description
- Parent category (if nested)
- Sort order
- Thread count

### Creating a Category

1. Click **Add New Category**.
2. Fill in:
   - **Name** (required)
   - **Slug** (auto-generated from name if blank)
   - **Description**
   - **Parent Category** (for nesting/hierarchy)
   - **Icon** (icon class or identifier)
   - **Color** (hex color code for display)
   - **Sort Order** (numeric, lower numbers display first)
3. Click **Create Category**.

### Category Hierarchy

Categories support parent-child relationships. Set a **Parent Category** when creating or editing a category to create a nested structure. This helps organize threads into logical groups and subgroups.

### Deleting Categories

- Click **Delete** to remove a category.
- Threads in a deleted category are not deleted but become uncategorized.

---

## Vote Management

Navigate to **CNW Social Bridge > Votes**.

### Viewing Votes

The vote list shows:

- Voter (user who cast the vote)
- Target type (Thread or Reply)
- Target ID and content preview
- Vote type (Upvote or Downvote)
- Date cast

### Deleting Votes

Admins can delete individual votes or bulk delete. This is useful for removing fraudulent or manipulated votes.

> **Note:** Deleting a vote adjusts the public vote count but does not automatically reverse reputation changes. Use the Reputation management section to adjust reputation if needed.

---

## Reputation Management

Navigate to **CNW Social Bridge > Reputation**.

### Viewing Reputation Entries

The reputation ledger shows all point transactions:

- User
- Action type (e.g., `thread_created`, `received_upvote`, `best_answer`)
- Description
- Points awarded/deducted
- Reason
- Date

### Creating a Reputation Entry

1. Click **Add New Entry**.
2. Select the **User**.
3. Choose the **Action Type**.
4. Enter a **Description**.
5. Set **Points** (positive for awards, negative for deductions).
6. Optionally enter a **Reason**.
7. Click **Create**.

This is useful for manually awarding bonus points or correcting reputation errors.

### Editing / Deleting Entries

- Click **Edit** to modify any entry.
- Click **Delete** to remove an entry.
- Bulk operations available.

### Reputation Point Actions

Common action types that automatically generate reputation:

| Action | Description |
|--------|-------------|
| `thread_created` | User created a new question |
| `reply_created` | User posted a reply |
| `received_upvote` | User received an upvote on their content |
| `received_downvote` | User received a downvote (negative points) |
| `best_answer` | User's reply was marked as the solution |
| `thread_saved` | User's thread was saved/marked helpful |

---

## Reports Management

Navigate to **CNW Social Bridge > Reports**.

### Viewing Reports

Reports submitted by users are listed with:

- Report type
- Subject
- Reporter
- Priority (Low, Medium, High)
- Status (Open, In Progress, Resolved, Closed)
- Date submitted

### Filtering Reports

Filter by:

- **Status**: Open, In Progress, Resolved, Closed
- **Priority**: Low, Medium, High

### Managing a Report

1. Click on a report to view full details.
2. Review the report type, description, and link to content.
3. Add **Admin Notes** to document your investigation.
4. Update the **Status**:
   - **Open** - Not yet reviewed.
   - **In Progress** - Under investigation.
   - **Resolved** - Action taken, issue addressed.
   - **Closed** - Report closed (no action needed or completed).
5. Click **Save**.

### Report Types

Users can report:

- Inappropriate Content
- Harassment or Bullying
- Spam or Self-Promotion
- Confidentiality Violation
- Misinformation or Harmful Advice
- Technical Issues / Bugs
- Other

---

## Take Action (Warnings & Suspensions)

Navigate to **CNW Social Bridge > Take Action**.

### Issuing a Warning

1. Search for the user by name or email.
2. Select the user.
3. Enter the **Reason** for the warning.
4. Click **Issue Warning**.

The warning is:

- Recorded in the database.
- Visible to the user on their profile under the "Warnings & Suspensions" tab.
- Logged in the activity feed for both users.
- A notification is sent to the warned user.

### Suspending a User

1. Search for and select the user.
2. Choose the suspension type:
   - **Temporary** - Specify the number of days.
   - **Permanent** - Indefinite suspension.
3. Enter the **Reason** for the suspension.
4. Click **Suspend User**.

While suspended, the user:

- Cannot create new threads.
- Cannot post replies.
- Cannot send direct messages.
- Sees a suspension notice when attempting these actions.
- Can still view content and their own profile.

Temporary suspensions are automatically lifted when the specified duration expires.

### Viewing Active Warnings & Suspensions

The Take Action page also shows:

- List of all active warnings.
- List of all active suspensions.
- Option to delete/revoke warnings.

---

## Guidelines Editor

Navigate to **CNW Social Bridge > Guidelines**.

### Editing Guidelines

1. Use the rich text editor to write your community guidelines.
2. Format text with headings, bold, italic, lists, and links.
3. Click **Save Guidelines**.

The guidelines are displayed to users on the **Guidelines** page in the forum frontend. This is the place to define:

- Community values and expectations.
- Content policies (what is and is not allowed).
- Consequences for violations.
- How to report issues.
- Contact information for administrators.

---

## Forum Settings

Navigate to **CNW Social Bridge > Settings**.

### Thread Settings

| Setting | Options | Description |
|---------|---------|-------------|
| Default Thread Status | Pending / Approved / Published | Controls whether new threads require moderator approval. **Pending** means all new threads must be approved before they become visible. **Approved** or **Published** means threads are immediately visible. |

### Reply Settings

| Setting | Options | Description |
|---------|---------|-------------|
| Default Reply Status | Pending / Approved / Published | Controls whether new replies require moderator approval. Same behavior as thread settings. |

### Saving Settings

Click **Save Settings** after making changes. Settings take effect immediately for all new content.

---

## Pusher / Real-Time Configuration

Navigate to **CNW Social Bridge > Settings** and scroll to the Pusher section.

### Why Configure Pusher?

Pusher (or a self-hosted alternative like Soketi) enables real-time features:

- **Live messaging** - Messages appear instantly without page refresh.
- **Online/offline status** - Green dot indicators on user avatars.
- **Typing indicators** - Shows when someone is typing a message.
- **Instant notifications** - Bell icon updates in real time.
- **Live status broadcasts** - Privacy setting changes take effect immediately.

### Pusher Settings

| Setting | Description |
|---------|-------------|
| Host | Pusher server hostname. For Pusher.com: leave default. For self-hosted (Soketi): enter your server address (e.g., `ws.yourdomain.com`). |
| Port | WebSocket port. Default: `443` for TLS. |
| Cluster | Pusher cluster region (e.g., `mt1`, `eu`, `ap1`). For self-hosted: `mt1`. |
| App ID | Your Pusher application ID. |
| Key | Your Pusher application key. |
| Secret | Your Pusher application secret (kept server-side only). |
| TLS | Enable/disable TLS encryption. Should be **enabled** for production. |

### Setting Up Pusher.com

1. Create an account at [pusher.com](https://pusher.com).
2. Create a new Channels app.
3. Copy the App ID, Key, Secret, and Cluster from the app dashboard.
4. Enter them in the plugin settings.
5. Save settings.

### Self-Hosted Alternative (Soketi)

If you prefer a self-hosted WebSocket server:

1. Install [Soketi](https://docs.soketi.app/) on your server.
2. Configure the Host, Port, App ID, Key, and Secret to match your Soketi configuration.
3. Set TLS appropriately based on your server setup.

---

## Logo Upload

Navigate to **CNW Social Bridge** (main dashboard page).

### Uploading a Logo

1. Click the **Upload Logo** button.
2. The WordPress Media Library opens.
3. Select an existing image or upload a new one.
4. Click **Use this image**.
5. The logo is saved and appears in the forum header.

### Removing the Logo

Click **Remove Logo** to revert to the default SVG wave logo.

**Recommended:** Use a logo image that is clear at small sizes (the header displays it at approximately 40px height).

---

## Import & Export

Navigate to **CNW Social Bridge > Import/Export**.

### Exporting Data

1. Click **Export All Data**.
2. A ZIP file is downloaded containing separate JSON files for all forum data:
   - Users
   - Threads
   - Replies
   - Messages
   - Tags
   - Categories
   - Votes
   - Reputation
   - Activity
   - Notifications
   - Saved threads
   - Followed tags
   - Settings

Use exports for:

- **Backups** - Regular data backups.
- **Migration** - Moving the forum to another WordPress installation.
- **Testing** - Creating test data sets.

### Importing Data

1. Click **Choose File** and select a previously exported ZIP file (or a legacy JSON file).
2. Click **Import**.
3. The import processes data in batches via AJAX to prevent timeouts.
4. Progress is displayed during import.
5. A summary is shown when complete.

**Import behavior:**

- Data is **added** to existing data, not replaced.
- Users are matched by email/login. If no match is found, new users are created.
- All IDs are remapped to prevent conflicts with existing data.
- Relationships between data types are preserved (e.g., thread-reply, user-thread).
- Import order: Users > Categories > Tags > Threads > Replies > Messages > Votes > Reputation > Activity > Notifications > Settings.

> **Warning:** Always back up your current data before importing to prevent data loss in case of issues.

---

## Roles & Capabilities

The plugin creates three custom WordPress roles on activation.

### Forum Member (`cnw_forum_member`)

Basic forum participation:

| Capability | Description |
|------------|-------------|
| `read` | View forum content |
| `cnw_create_threads` | Post new questions |
| `cnw_reply_threads` | Reply to threads |
| `cnw_send_messages` | Send direct messages |
| `cnw_edit_own_posts` | Edit own threads and replies |
| `cnw_delete_own_posts` | Delete own threads and replies |

### Moderator (`cnw_moderator`)

All Forum Member capabilities plus:

| Capability | Description |
|------------|-------------|
| `cnw_edit_any_post` | Edit any thread or reply |
| `cnw_delete_any_post` | Delete any thread or reply |
| `cnw_close_threads` | Close or reopen threads |
| `cnw_pin_threads` | Pin or unpin threads |
| `cnw_approve_replies` | Approve pending replies |
| `cnw_warn_users` | Issue warnings to users |

### Forum Admin (`cnw_forum_admin`)

All Moderator capabilities plus:

| Capability | Description |
|------------|-------------|
| `cnw_ban_users` | Suspend or ban users |
| `cnw_manage_settings` | Configure plugin settings |
| `cnw_manage_roles` | Manage user roles |
| `cnw_view_reports` | View and manage reports |

### WordPress Administrator

WordPress administrators (`manage_options` capability) have full access to all admin pages and features regardless of the custom roles above.

### Assigning Roles

Assign custom roles to users through the standard WordPress user editor:

1. Go to **Users** in the WordPress admin.
2. Edit the user.
3. Change their role to one of the CNW roles.
4. Click **Update User**.

A user can also have a standard WordPress role alongside the forum role.

---

## Database Tables

The plugin creates the following tables (prefixed with your WordPress table prefix, typically `wp_`):

| Table | Purpose |
|-------|---------|
| `cnw_social_worker_threads` | All questions/threads |
| `cnw_social_worker_replies` | All replies/answers |
| `cnw_social_worker_messages` | Direct messages |
| `cnw_social_worker_categories` | Thread categories |
| `cnw_social_worker_tags` | Topic tags |
| `cnw_social_worker_thread_tags` | Thread-to-tag relationships |
| `cnw_social_worker_user_followed_tags` | User tag subscriptions |
| `cnw_social_worker_votes` | Upvotes and downvotes |
| `cnw_social_worker_reputation` | Reputation point ledger |
| `cnw_social_worker_activity` | User activity log |
| `cnw_social_worker_notifications` | In-app notifications |
| `cnw_social_worker_saved_threads` | Bookmarked threads |
| `cnw_social_worker_reports` | User-submitted reports |
| `cnw_social_worker_connections` | User connections (friends) |
| `cnw_social_worker_restrictions` | User restrictions/blocks |
| `cnw_social_worker_warnings` | Warnings and suspensions |

All tables are created automatically on plugin activation. They are **not** removed on deactivation to preserve data. To remove all data, delete the plugin.

---

## Troubleshooting

### Forum Page Shows Blank Content

- Ensure the `[cnw_social_bridge]` shortcode is added to the page.
- Check the browser console for JavaScript errors.
- Verify that the `dist/js/app.js` and `dist/css/app.css` files exist.
- Clear any caching plugins (page cache, object cache).

### Real-Time Features Not Working

- Verify Pusher settings are correctly configured in **Settings**.
- Check that the Pusher Key, Secret, and App ID match your Pusher dashboard.
- Ensure WebSocket connections are not blocked by your server's firewall.
- Check the browser console for WebSocket connection errors.
- If self-hosting (Soketi), verify the server is running and accessible.

### New Posts Not Appearing

- Check the **Default Thread/Reply Status** in Settings.
- If set to "Pending," new posts require admin approval before becoming visible.
- Go to **Threads** or **Replies** in the admin and approve pending items.

### Users Cannot Send Messages

- Check the recipient's **Message Privacy** setting.
- Default is "Connections Only" — users must be connected to message each other.
- Users can change this to "Everyone" in their profile Settings tab.

### Styles Look Broken on Direct Page Load

- Clear your browser cache and reload.
- If using a caching plugin, clear the plugin cache.
- Rebuild assets by running `npm run build` in the plugin directory.

### Import Fails or Times Out

- Ensure the ZIP file was exported from the same plugin version.
- Check PHP's `max_upload_size` and `post_max_size` settings.
- The import uses AJAX batching to prevent timeouts, but very large datasets may still need PHP time limit adjustments.
- Check the browser console for errors during import.

### Email Notifications Not Sending

- Verify WordPress can send emails (test with another plugin like WP Mail SMTP).
- Check user notification preferences — they may have email notifications disabled.
- The email digest cron runs every 5 minutes. Ensure WordPress cron is working (`wp-cron.php` or a real server cron job).

### Performance Optimization

- **Caching**: Use a page caching plugin but exclude the forum page from full-page caching (since it relies on dynamic JavaScript).
- **Object caching**: Compatible with Redis/Memcached object caching.
- **CDN**: Static assets (`dist/js/app.js`, `dist/css/app.css`) can be served via CDN.
- **Database**: For large forums (10,000+ threads), ensure proper MySQL indexing. The plugin creates indexes on key columns automatically.

---

## Support

For technical support, bug reports, or feature requests, contact the plugin developer or submit an issue through the appropriate support channel.
