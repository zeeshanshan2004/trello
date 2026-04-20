<?php
$file = 'resources/views/boards/show.blade.php';
$content = file_get_contents($file);

// The blade file uses LF, so we search with LF
$n = "\n";

// Fix 1: Remove Board Members heading
$old1 = 'font-size: 12px; font-weight: 600; color: #9fadbc; text-transform: uppercase; margin-bottom: 12px; letter-spacing: 0.5px;">Board Members</div>';
if (strpos($content, $old1) !== false) {
    // Remove the entire heading div line
    $content = preg_replace('/<div style="font-size: 12px; font-weight: 600; color: #9fadbc; text-transform: uppercase; margin-bottom: 12px; letter-spacing: 0\.5px;">Board Members<\/div>\n/', '', $content);
    // Also change the padding of the parent div
    $content = str_replace(
        'padding: 16px; border-bottom: 1px solid #3c444d;"><div id="boardMembersList" style="display: flex; flex-direction: column; gap: 8px; max-height: 250px; overflow-y: auto;">',
        'padding: 12px 16px; border-bottom: 1px solid #3c444d;"><div id="boardMembersList" style="display: flex; flex-direction: column; gap: 6px; max-height: 200px; overflow-y: auto;">',
        $content
    );
    echo "Fix 1 done: Board Members heading removed\n";
} else {
    echo "Fix 1: Pattern not found\n";
}

// Fix 2: Update Pending Approvals section
$old2 = '>Pending Approvals</div>';
if (strpos($content, $old2) !== false) {
    // Change the outer div to have id and be hidden
    $content = str_replace(
        '<div style="padding: 16px; border-bottom: 1px solid #3c444d;">' . $n . '                            <div style="font-size: 12px; font-weight: 600; color: #9fadbc; text-transform: uppercase; margin-bottom: 12px; letter-spacing: 0.5px;">Pending Approvals</div>',
        '<div id="pendingRequestsSection" style="padding: 12px 16px; border-bottom: 1px solid #3c444d; display: none;">' . $n . '                            <div style="font-size: 11px; font-weight: 700; color: #e2b200; text-transform: uppercase; margin-bottom: 10px; letter-spacing: 0.5px;">&#9203; Pending Access Requests</div>',
        $content
    );
    echo "Fix 2 done: Pending section updated\n";
} else {
    echo "Fix 2: Pattern not found\n";
}

// Fix 3: Remove </div>div> typo
if (strpos($content, '</div>div>') !== false) {
    $content = str_replace('</div>div>' . $n . '            </div>' . $n . '            @if($canEdit ?? false)', '</div>' . $n . '            @if($canEdit ?? false)', $content);
    echo "Fix 3 done: div typo removed\n";
} else {
    echo "Fix 3: No typo found (may already be fixed)\n";
}

// Fix 4: Add loadPendingRequests() in toggleMembersDropdown
$old4 = '                loadBoardMembers();' . $n . '                loadExistingShareLink();';
if (strpos($content, $old4) !== false) {
    $content = str_replace($old4, '                loadBoardMembers();' . $n . '                loadPendingRequests();' . $n . '                loadExistingShareLink();', $content);
    echo "Fix 4 done: loadPendingRequests added\n";
} else {
    echo "Fix 4: Pattern not found\n";
}

file_put_contents($file, $content);
echo "All done!\n";
