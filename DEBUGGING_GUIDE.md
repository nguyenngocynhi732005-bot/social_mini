# NEWSFEED DEBUGGING GUIDE

## Code Analysis & Expected Results

Based on analysis of the `newsfeed.blade.php` code, here's what you should verify:

---

## 1. TEST JS BUTTON - Expected Alert Message

**Button location:** Top right of the post composer card (bug icon with "Test JS" label)

**Expected alert:** 
```
TEST: JavaScript works! Quill=function, openComposer=function
```

**What this checks:**
- `Quill=function` → Quill library successfully loaded from CDN (https://cdn.quilljs.com/1.3.6/quill.js)
- `openComposer=function` → JavaScript functions are properly defined

**If you see something different:**
- If `Quill=undefined`: Quill CDN failed to load - check browser console for CSP/CORS errors
- If `openComposer=undefined`: Initialization script didn't run - check console for errors

---

## 2. BROWSER CONSOLE CHECK

**What to look for:**
- Open DevTools (F12/Ctrl+Shift+I)
- Go to Console tab
- Look for ANY red error messages

**Expected console logs on page load (in order):**
1. `DOMContentLoaded triggered`
2. `initGeneralFeatures: function`
3. `initPostComposer: function`
4. `initVideoComposer: function`
5. `initPostComposer starting...`
6. `editor element: <div id="editor"...`
7. `All init functions called`

**Errors to watch for:**
- CORS errors fetching scripts
- `Quill is not defined` → CDN failed
- `bootstrap is not defined` → Bootstrap JS failed to load
- `TypeError: Cannot read property 'getElementById'` → DOM not ready
- `Failed to load resource` → Missing files

---

## 3. CLICKING "ÁNH/VIDEO" BUTTON (Camera/Images icon)

**Button location:** First button in the quick action row below post composer

**Expected behavior:**
1. Post modal should open (titled "Tạo bài viết")
2. File picker dialog should appear to select image/video
3. Console logs: 
   - `quickMediaBtn clicked`
   - `openComposer(function)` called

**Console logs you should see:**
- `quickMediaBtn: <button>...`
- `Binding quickMediaBtn listener` (first time only)
- `quickMediaBtn clicked` (every click)

**Issues that might occur:**
- **Modal doesn't open**: Bootstrap JS not loaded, or `openComposer` function has errors
- **File picker doesn't open**: `postMedia` input element not found
- **No console logs**: Event listener not attached

---

## 4. CLICKING "QUAY VIDEO" BUTTON (Video icon)

**Button location:** First button in the quick action row (video icon)

**Expected behavior:**
1. Video modal should open (titled "Quay hoặc tải video")
2. Modal should show video recording interface with:
   - Max duration selector (2/3/5 min options)
   - Record button
   - Video preview area

**Console log:**
- `Quay video clicked`

**Issues that might occur:**
- **Modal doesn't open**: Check if #videoModal exists
- **Modal is blank**: `initVideoComposer()` failed, check console for errors
- **No recording controls**: Quill editor failed to initialize in video modal

---

## 5. DEBUG PANEL (Bottom-right corner)

**Location:** Fixed position in bottom-right corner of screen

**Appearance:** 
- Black background with green text (#1a1a1a, #00ff00)
- Terminal-like monospace font
- Scrollable list of logs
- Red X button in top-right to close

**What logs should appear:**
1. Initial page load logs (DOMContentLoaded events)
2. Component initialization logs (initPostComposer, initVideoComposer, etc.)
3. Button click logs (quickMediaBtn clicked, Quay video clicked)
4. File selection logs (when you pick media)
5. Upload progress logs (during file upload)

**If debug panel is missing:**
- Page didn't parse correctly
- JavaScript error prevented initialization
- Check console for errors

---

## Step-by-Step Testing Procedure

1. **Open Developer Console First**
   - Press F12 or Ctrl+Shift+I
   - Keep it open during all tests
   - Switch to Console tab
   - Note: Do NOT close this, you need to watch for errors

2. **Verify Quill & openComposer**
   - Click "Test JS" button
   - Note the exact alert message
   - Record what you see

3. **Verify Console Logs**
   - Note any errors (red text)
   - Compare with "Expected console logs" section above
   - If errors exist, note the exact message

4. **Test Ảnh/video Button**
   - Watch console
   - Click "Ảnh/video" button
   - Record: Did modal open? Did file picker open?
   - Note any console errors

5. **Test Quay video Button**
   - Watch console  
   - Click "Quay video" button
   - Record: Did modal open? Is it complete (with video controls)?
   - Note any console errors

6. **Check Debug Panel**
   - Look bottom-right of screen
   - Is it visible?
   - What logs appear?
   - Copy/paste key log messages

---

## Using the Debug Script

1. Copy all code from `/DEBUG_SCRIPT.js` in this project
2. Paste into browser console (F12 > Console tab)
3. Press Enter
4. Script will output all required information automatically
5. Check `window.debugResults` for a summary

---

## Reporting Format

When you've completed the testing, provide:

**1. Test JS Alert:**
- Expected: `TEST: JavaScript works! Quill=function, openComposer=function`
- Actual: [What you saw]
- Matches: YES/NO

**2. Quill Status:**
- Loaded: YES/NO
- Evidence: [Alert message or console output]

**3. openComposer Status:**
- Exists: YES/NO
- Type: function/undefined/other

**4. Console Errors:**
- Count: 0 or [number]
- Errors: [List them]

**5. Ảnh/video Button:**
- Modal opens: YES/NO
- File picker opens: YES/NO
- Console errors: YES/NO [list them]

**6. Quay video Button:**
- Modal opens: YES/NO
- Video controls visible: YES/NO
- Console errors: YES/NO [list them]

**7. Debug Panel:**
- Visible: YES/NO
- Logs visible: YES/NO
- Key logs: [List a few you see]

---

## Common Issues & Solutions

| Issue | Cause | Solution |
|-------|-------|----------|
| Quill undefined | CDN failed to load | Check internet, CORS settings, CDN URL in blade file |
| openComposer undefined | Script didn't execute | Check for JavaScript errors, verify DOMContentLoaded event fired |
| Modal won't open | Bootstrap JS not loaded | Check bootstrap.bundle.js is loaded in layout file |
| Debug panel missing | Console.log interceptor failed | Check if script executed, look for errors in that section |
| File picker won't open | postMedia input not found | Verify post-modal.blade.php is included and id="postMedia" exists |

---

## Code Files to Reference

- Main view: `resources/views/pages/newsfeed.blade.php`
- Post modal: `resources/views/components/modals/post-modal.blade.php`  
- Video modal: `resources/views/components/modals/video-modal.blade.php`
- Video logic: `public/js/video-composer.js`
- Recorder logic: `public/js/video-recorder.js`
