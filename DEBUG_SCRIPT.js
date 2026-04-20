// COMPREHENSIVE DEBUG SCRIPT FOR NEWSFEED
// Paste this entire script into the browser console while on http://localhost:8000/newsfeed

console.log("=== NEWSFEED DEBUGGING SCRIPT START ===\n");

// 1. Check Quill library
console.log("1. QUILL LIBRARY CHECK:");
console.log("   typeof Quill:", typeof Quill);
console.log("   Quill available:", typeof Quill !== 'undefined' ? "✓ YES" : "✗ NO");

// 2. Check openComposer function
console.log("\n2. OPENCOMPOSER FUNCTION CHECK:");
console.log("   typeof openComposer:", typeof openComposer);
console.log("   openComposer exists:", typeof openComposer === 'function' ? "✓ YES (FUNCTION)" : typeof openComposer !== 'undefined' ? "DEFINED BUT NOT FUNCTION: " + typeof openComposer : "✗ NO (UNDEFINED)");

// 3. Simulate Test JS button
console.log("\n3. SIMULATING TEST JS BUTTON:");
const testMsg = 'TEST: JavaScript works! Quill=' + (typeof Quill) + ', openComposer=' + (typeof openComposer);
console.log("   Alert would show: " + testMsg);

// 4. Check for JavaScript errors
console.log("\n4. CHECKING FOR ERRORS:");
// Try to find errors by checking if Quill loaded properly
if (typeof Quill !== 'undefined') {
    console.log("   ✓ Quill loaded successfully");
} else {
    console.log("   ✗ Quill NOT loaded - check network tab for CDN failures");
}

// Check if modals exist
const postModal = document.getElementById('postModal');
const videoModal = document.getElementById('videoModal');
console.log("   Post modal exists:", postModal ? "✓ YES" : "✗ NO");
console.log("   Video modal exists:", videoModal ? "✓ YES" : "✗ NO");

// 5. Test clicking "Ảnh/video" button
console.log("\n5. TESTING ÁNH/VIDEO BUTTON:");
const quickMediaBtn = document.getElementById('quickMediaBtn');
if (quickMediaBtn) {
    console.log("   Button found: ✓ YES");
    console.log("   onclick handler:", quickMediaBtn.onclick ? "✓ YES" : "✗ NO");
    // Don't actually click, just check
    console.log("   What should happen: Opens post modal and file picker");
} else {
    console.log("   Button found: ✗ NO");
}

// 6. Test clicking "Quay video" button
console.log("\n6. TESTING QUAY VIDEO BUTTON:");
const videoBtn = document.querySelector('button[data-bs-target="#videoModal"]');
if (videoBtn) {
    console.log("   Button found: ✓ YES");
    console.log("   Modal target: #videoModal");
    console.log("   What should happen: Opens video modal");
} else {
    console.log("   Button found: ✗ NO");
}

// 7. Test clicking "Test JS" button
console.log("\n7. TESTING TEST JS BUTTON:");
const testJsBtn = document.querySelector('button[onclick*="TEST: JavaScript"]');
if (testJsBtn) {
    console.log("   Button found: ✓ YES");
    console.log("   Button will show alert with: " + testMsg);
} else {
    console.log("   Button found: ✗ NO");
}

// 8. Check debug panel
console.log("\n8. DEBUG PANEL CHECK:");
const debugPanel = document.getElementById('debugPanel');
if (debugPanel) {
    console.log("   Debug panel found: ✓ YES");
    console.log("   Currently visible:", debugPanel.style.display !== 'none' ? "YES" : "NO");
    const debugLog = document.getElementById('debugLog');
    if (debugLog) {
        const logCount = debugLog.children.length;
        console.log("   Log entries count:", logCount);
        if (logCount > 0) {
            console.log("   First few logs:", Array.from(debugLog.children).slice(0, 3).map(el => el.textContent));
        }
    }
} else {
    console.log("   Debug panel found: ✗ NO");
}

// 9. Check all required elements
console.log("\n9. ALL REQUIRED ELEMENTS:");
const elements = {
    'postModal': 'postModal',
    'videoModal': 'videoModal',
    'quickMediaBtn': 'quickMediaBtn',
    'editor': 'editor',
    'videoEditor': 'videoEditor',
    'postMedia': 'postMedia',
    'videoMedia': 'videoMedia',
    'debugPanel': 'debugPanel'
};

let allFound = true;
for (const [name, id] of Object.entries(elements)) {
    const element = document.getElementById(id);
    const status = element ? "✓" : "✗";
    console.log(`   ${status} ${name}`);
    if (!element) allFound = false;
}

console.log("\n=== DEBUGGING COMPLETE ===");
console.log("All elements found:", allFound ? "✓ YES" : "✗ NO (check console for missing elements)");

// Store results globally for easy access
window.debugResults = {
    quillLoaded: typeof Quill !== 'undefined',
    openComposerExists: typeof openComposer === 'function',
    testMessage: testMsg,
    postModalExists: !!postModal,
    videoModalExists: !!videoModal,
    helpText: "Now manually test:\n1. Click Test JS button and note the alert\n2. Click Ảnh/video button - should open post modal\n3. Click Quay video button - should open video modal\n4. Check debug panel (bottom-right) for logs"
};

console.log("\nQuick reference - check window.debugResults");
