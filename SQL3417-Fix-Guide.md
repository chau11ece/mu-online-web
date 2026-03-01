# SQL Server Error 3417 - Master Database Access Issue

## Quick Fix Script
Run this first (as Administrator):
```powershell
cd C:\MU-Project
.\Fix-SQL3417.ps1
```

## What Error 3417 Means
SQL Server cannot access or initialize the master database files (`master.mdf` and `mastlog.ldf`). This prevents SQL Server from starting.

## Common Causes & Solutions

### 1. **Read-Only Files** (Most Common)
**Fix:** The script will automatically remove read-only attributes. If it doesn't work:
- Navigate to: `C:\Program Files\Microsoft SQL Server\MSSQL11.SQLEXPRESS\MSSQL\DATA`
- Right-click `master.mdf` → Properties → Uncheck "Read-only"
- Do the same for `mastlog.ldf` and all other `.mdf`/`.ldf` files
- Apply to all files in subfolders

### 2. **Permission Issues**
**Fix:** The script will fix permissions. Manual fix:
- Right-click `DATA` folder → Properties → Security tab
- Add: `NT SERVICE\MSSQL$SQLEXPRESS` with **Full Control**
- Check "Replace all child object permissions"
- Apply to all subfolders

### 3. **Corrupted Master Database**
**Solution:** Rebuild master database (see below) or reinstall SQL Server

### 4. **Anti-Virus Interference**
- Temporarily disable anti-virus
- Add SQL Server folders to exclusions:
  - `C:\Program Files\Microsoft SQL Server\`
  - `C:\Program Files (x86)\Microsoft SQL Server\`

### 5. **Disk Issues**
- Run `chkdsk C: /f` (requires restart)
- Check disk for bad sectors

## Option 1: Rebuild Master Database (Advanced)

If the master database is corrupted, you can rebuild it:

1. **Stop SQL Server service** (if running):
   ```powershell
   Stop-Service 'MSSQL$SQLEXPRESS'
   ```

2. **Run SQL Server setup in rebuild mode**:
   ```powershell
   cd "C:\Program Files\Microsoft SQL Server\110\Setup Bootstrap\SQLServer2012"
   .\Setup.exe /QUIET /ACTION=REBUILDDATABASE /INSTANCENAME=SQLEXPRESS /SQLSYSADMINACCOUNTS="BUILTIN\Administrators"
   ```

   **Note:** Path may vary. Find your SQL Server installation:
   - Look in: `C:\Program Files\Microsoft SQL Server\110\` (SQL 2012)
   - Or: `C:\Program Files\Microsoft SQL Server\120\` (SQL 2014)
   - Or: `C:\Program Files\Microsoft SQL Server\130\` (SQL 2016)

3. **After rebuild, restore your databases** (if you had any)

## Option 2: Reinstall SQL Server (Recommended if rebuild fails)

### Before Reinstalling:
1. **Backup existing databases** (if any):
   - Copy entire `DATA` folder: `C:\Program Files\Microsoft SQL Server\MSSQL11.SQLEXPRESS\MSSQL\DATA`
   - Copy entire `LOG` folder: `C:\Program Files\Microsoft SQL Server\MSSQL11.SQLEXPRESS\MSSQL\Log`

2. **Uninstall SQL Server Express 2012**:
   - Control Panel → Programs → Uninstall
   - Remove all SQL Server components

3. **Clean up** (optional but recommended):
   ```powershell
   # Remove leftover folders (backup first!)
   Remove-Item "C:\Program Files\Microsoft SQL Server\MSSQL11.SQLEXPRESS" -Recurse -Force
   ```

4. **Download SQL Server Express 2012**:
   - Microsoft Download Center
   - Or use SQL Server Express 2019/2022 (newer, but may need config changes)

5. **Reinstall**:
   - Use instance name: **SQLEXPRESS** (to match your config)
   - Use Mixed Mode authentication
   - Set SA password (or use Windows Authentication)

6. **Restore databases** (if you backed them up)

## After Fixing

Test SQL Server:
```powershell
Start-Service 'MSSQL$SQLEXPRESS'
Get-Service 'MSSQL$SQLEXPRESS'
```

If running, test connection:
```powershell
sqlcmd -S localhost\SQLEXPRESS -E -Q "SELECT @@VERSION"
```

## Still Having Issues?

1. **Check Windows Event Viewer**:
   - `Win + R` → `eventvwr.msc`
   - Windows Logs → Application
   - Look for SQL Server errors

2. **Check SQL Server Error Log**:
   - `C:\Program Files\Microsoft SQL Server\MSSQL11.SQLEXPRESS\MSSQL\Log\ERRORLOG`

3. **Contact Support**:
   - SQL Server forums
   - Microsoft Support
