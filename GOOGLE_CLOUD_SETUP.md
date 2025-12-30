# Google Cloud Project Setup Guide

This guide will walk you through setting up Google Cloud Project and OAuth credentials for DriveSpy.

---

## Step 1: Create Google Cloud Project

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Click **Select a project** dropdown (top left)
3. Click **NEW PROJECT**
4. Enter project details:
   - **Project name:** `DriveSpy` (or your preferred name)
   - **Organization:** (optional)
   - **Location:** (optional)
5. Click **CREATE**
6. Wait for the project to be created (you'll see a notification)

---

## Step 2: Enable Google Drive API

1. Make sure your new project is selected (check top left dropdown)
2. Go to **APIs & Services** > **Library** (or use [this link](https://console.cloud.google.com/apis/library))
3. Search for **"Google Drive API"**
4. Click on **Google Drive API**
5. Click **ENABLE**
6. Wait for the API to be enabled

---

## Step 3: Configure OAuth Consent Screen

1. Go to **APIs & Services** > **OAuth consent screen** (or use [this link](https://console.cloud.google.com/apis/credentials/consent))
2. Select **User Type:**
   - Choose **External** (for testing with any Google account)
   - Or **Internal** (if you have a Google Workspace organization)
3. Click **CREATE**

### Fill in OAuth Consent Screen Details:

**App information:**
- **App name:** `DriveSpy`
- **User support email:** Your email address
- **App logo:** (optional, can skip for now)

**App domain:**
- **Application home page:** `http://drivespy.test` (or your domain)
- **Application privacy policy link:** (optional for testing)
- **Application terms of service link:** (optional for testing)

**Authorized domains:**
- Click **+ ADD DOMAIN**
- Add: `drivespy.test` (without http://)

**Developer contact information:**
- **Email addresses:** Your email address

4. Click **SAVE AND CONTINUE**

### Scopes:

1. Click **ADD OR REMOVE SCOPES**
2. Filter/search for these scopes:
   - `https://www.googleapis.com/auth/drive.readonly` - View and download files in Google Drive
   - `https://www.googleapis.com/auth/drive.metadata.readonly` - View metadata of files in Google Drive
3. Click **UPDATE**
4. Click **SAVE AND CONTINUE**

### Test users (for External apps):

1. Click **+ ADD USERS**
2. Add your Google account email (the one you'll use for testing)
3. Click **ADD**
4. Click **SAVE AND CONTINUE**

5. Review and click **BACK TO DASHBOARD**

---

## Step 4: Create OAuth 2.0 Credentials

1. Go to **APIs & Services** > **Credentials** (or use [this link](https://console.cloud.google.com/apis/credentials))
2. Click **+ CREATE CREDENTIALS** (top)
3. Select **OAuth client ID**

### Configure OAuth Client:

1. **Application type:** Select **Web application**
2. **Name:** `DriveSpy Web Client` (or any name you prefer)

3. **Authorized JavaScript origins:**
   - Click **+ ADD URI**
   - Add: `http://drivespy.test`
   - (Add `http://localhost:8000` if using different local server)

4. **Authorized redirect URIs:**
   - Click **+ ADD URI**
   - Add: `http://drivespy.test/auth/google/callback`
   - **IMPORTANT:** This must match exactly with the route in your application

5. Click **CREATE**

### Download Credentials:

1. A popup will appear with your **Client ID** and **Client Secret**
2. **COPY** both values - you'll need them in the next step
3. Optionally click **DOWNLOAD JSON** to save a backup

**Keep these credentials secure! Never commit them to version control.**

---

## Step 5: Add Credentials to Your Application

1. Open `/var/www/DriveSpy/.env` file
2. Add these lines (values will be added automatically):

```env
GOOGLE_CLIENT_ID=your_client_id_here
GOOGLE_CLIENT_SECRET=your_client_secret_here
GOOGLE_REDIRECT_URI=http://drivespy.test/auth/google/callback
```

**Note:** The actual values will be set up automatically in the next implementation step.

---

## Step 6: Publish Your App (When Ready for Production)

For development/testing, your app remains in "Testing" status. To make it available to all users:

1. Go to **OAuth consent screen**
2. Click **PUBLISH APP**
3. Click **CONFIRM**
4. Submit for verification (required for accessing user data)

**For now, keep it in Testing mode** - this allows up to 100 test users.

---

## Troubleshooting

### "Access blocked: This app's request is invalid"
- Check that your redirect URI in Google Cloud exactly matches the one in your app
- Make sure the domain is added to **Authorized domains** in OAuth consent screen

### "This app isn't verified"
- Click **Advanced** > **Go to DriveSpy (unsafe)** during testing
- For production, submit app for verification

### "Required scope not requested"
- Go back to OAuth consent screen and ensure all required scopes are added
- Re-authorize the application

---

## What's Next?

After completing these steps:

1. Copy your **Client ID** and **Client Secret**
2. Keep them ready - the application will ask for them during setup
3. The app will automatically configure the `.env` file
4. You'll be able to connect Google accounts and start monitoring Drive folders

---

## Security Notes

- Never share your Client Secret publicly
- Never commit credentials to git
- The `.env` file is already in `.gitignore`
- Regularly rotate your credentials in production
- Use environment-specific credentials (dev/staging/production)

---

## Resources

- [Google Cloud Console](https://console.cloud.google.com/)
- [Google Drive API Documentation](https://developers.google.com/drive/api/v3/about-sdk)
- [OAuth 2.0 Documentation](https://developers.google.com/identity/protocols/oauth2)
