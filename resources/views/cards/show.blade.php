<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $card->title }} - {{ $board->name }}</title>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="{{ asset('css/Show_blade.css') }}">


</head>

<body>
    <div class="card-view-overlay" onclick="closeCardView()">
        <div class="card-view-container" onclick="event.stopPropagation()">
            @php
                $cover = $card->cover ?? '';
                $hasCover = !empty($cover) && $cover !== '/storage/' && $cover !== 'storage/';
                $coverStyle = $hasCover
                    ? (str_starts_with($cover, 'http') || str_starts_with($cover, 'data:')
                        ? 'background-image: url(' . e($cover) . ');'
                        : 'background: ' . e($cover) . ';')
                    : '';

                // Better mapping of card labels using boardLabels
                $cardLabelsRaw = $card->labels ?? [];
                $cardLabels = [];
                if (is_array($cardLabelsRaw)) {
                    foreach ($cardLabelsRaw as $labelRef) {
                        if (is_numeric($labelRef)) {
                            $found = collect($boardLabels)->firstWhere('id', (int) $labelRef);
                            if ($found)
                                $cardLabels[] = $found;
                        } elseif (is_array($labelRef)) {
                            $cardLabels[] = (object) $labelRef;
                        } else {
                            $cardLabels[] = $labelRef;
                        }
                    }
                }

                $labelColorsMap = [
                    'green' => '#61bd4f',
                    'yellow' => '#f2d600',
                    'orange' => '#ff9f1a',
                    'red' => '#eb5a46',
                    'purple' => '#c377e0',
                    'blue' => '#0079bf',
                ];
            @endphp


            @if($hasCover)
                @php
                    $isColor = preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $cover);
                    $coverUrl = $cover;
                    if (!$isColor) {
                        if (str_starts_with($cover, 'http') || str_starts_with($cover, 'data:')) {
                            $coverUrl = $cover;
                        } else {
                            // Clean the path and resolve via Storage::url()
                            $cleanPath = str_replace(['/storage/', 'storage/'], '', $cover);
                            $coverUrl = Storage::url($cleanPath);
                        }
                    }
                @endphp
                @if($isColor)
                    <div class="card-cover-display" style="background-color: {{ $cover }};"></div>
                @else
                    <div class="card-cover-display cover-image"
                        style="--cover-url: url('{{ $coverUrl }}'); background-image: url('{{ $coverUrl }}');">
                        <img src="{{ $coverUrl }}" alt="cover"
                            onerror="console.error('Cover failed to load:', '{{ $coverUrl }}'); this.closest('.card-cover-display').style.display='none'">
                    </div>
                @endif
            @else
                <div class="card-cover-display no-cover" style="height: 8px;"></div>
            @endif







            <!-- Invisible color bar below cover -->
            <div class="card-color-bar" id="cardColorBar"
                style="height: 8px; width: 100%; background: transparent; display: none;"></div>

            <div class="card-header-top">
                <div class="list-dropdown" onclick="event.stopPropagation(); openListDropdown()">
                    <span>{{ $list->name }}</span>
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M7 10l5 5 5-5z" />
                    </svg>
                </div>
                <div class="header-actions">
                    <button class="header-icon-btn" onclick="event.stopPropagation(); openCoverModal()"
                        title="Change cover">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zm-5.04-6.71l-2.75 3.54-1.96-2.36L6.5 17h11l-3.54-4.71z" />
                        </svg>
                    </button>
                    <div style="position: relative;">
                        <button class="header-icon-btn" onclick="event.stopPropagation(); openMoreMenu()"
                            title="More options">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path
                                    d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" />
                            </svg>
                        </button>
                        <!-- More Options Menu - anchored to button -->
                        <div id="moreMenu"
                            style="display:none; position:absolute; top:calc(100% + 4px); right:0; background:#282e33; border:1px solid #38414a; border-radius:8px; min-width:150px; box-shadow:0 8px 24px rgba(0,0,0,0.5); z-index:9999; padding:4px 0;">
                            <div class="dropdown-item" onclick="openMoveModal()">Move</div>
                            @if($canDelete)
                                <div class="dropdown-item" onclick="archiveCard()">Archive</div>
                                <div class="dropdown-item danger" onclick="deleteCard()">Delete</div>
                            @endif
                        </div>
                    </div>
                    <button class="header-icon-btn" onclick="closeCardView()" title="Close">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" />
                        </svg>
                    </button>
                    <div class="user-avatar-header" id="userAvatar" onclick="toggleUserDropdown()">
                        {{ substr(auth()->user()->name, 0, 2) }}
                        <div class="user-dropdown" id="userDropdown">
                            <div class="dropdown-user-info">
                                <strong>{{ auth()->user()->name }}</strong><br>
                                {{ auth()->user()->email }}
                            </div>
                            <a href="{{ route('profile.show') }}" class="dropdown-item">Profile & Settings</a>
                            <div class="dropdown-divider"></div>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                            <a href="#" class="dropdown-item"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                Log out
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-view-content">
                <div class="card-view-inner"
                    style="display: flex; gap: 0; padding: 0; min-height: 500px; overflow: hidden;">

                    <!-- Left Column (Main Content) -->
                    <div class="card-view-main" style="flex: 1; min-width: 0; padding: 54px; background: #22272b;">

                        <!-- Invisible Inputs -->
                        <input type="file" id="attachmentFileInput" style="display: none;" multiple
                            onchange="uploadAttachment(event)">
                        <input type="file" id="coverImageUpload" accept="image/*" style="display: none;"
                            onchange="uploadCoverImage(event)">

                        <!-- Title Section -->
                        <div class="card-title-section"
                            style="margin-bottom: 24px; display: flex; align-items: flex-start; gap: 12px;">
                            <svg viewBox="0 0 24 24" fill="currentColor"
                                style="width: 24px; height: 24px; color: #9fadbc; margin-top: 4px; flex-shrink: 0;">
                                <path
                                    d="M20 18c1.1 0 1.99-.9 1.99-2L22 6c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2H0v2h24v-2h-4zM4 6h16v10H4V6z" />
                            </svg>
                            <div style="flex: 1;">
                                <input type="text" id="cardTitleInput" value="{{ $card->title }}"
                                    onblur="updateCardTitle()" onkeypress="if(event.key==='Enter') this.blur()"
                                    style="width: 100%; border: none; background: transparent; font-size: 24px; font-weight: 700; color: #b6c2cf; outline: none; padding: 0;">
                                <!-- <div style="font-size: 14px; color: #9fadbc; margin-top: 4px;">in list <span style="text-decoration: underline; cursor: pointer;" onclick="openListDropdown()">{{ $list->name }}</span></div> -->
                            </div>
                        </div>
                        <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 40px; padding-left: 36px;">
                            <button class="sidebar-btn" onclick="openMembersModal()" style="width:auto; margin:0;"><svg
                                    viewBox="0 0 24 24" fill="currentColor">
                                    <path
                                        d="M15,14C12.33,14 7,15.33 7,18V20H23V18C23,15.33 17.67,14 15,14M6,10V7H4V10H1V12H4V15H6V12H9V10M15,12A4,4 0 0,0 19,8A4,4 0 0,0 15,4A4,4 0 0,0 11,8A4,4 0 0,0 15,12Z" />
                                </svg> Members</button>
                            <button class="sidebar-btn" onclick="openLabelsModal()" style="width:auto; margin:0;"><svg
                                    viewBox="0 0 24 24" fill="currentColor">
                                    <path
                                        d="M21.41 11.58l-9-9C12.05 2.22 11.55 2 11 2H4c-1.1 0-2 .9-2 2v7c0 .55.22 1.05.59 1.42l9 9c.36.36.86.58 1.41.58.55 0 1.05-.22 1.41-.59l7-7c.37-.36.59-.86.59-1.41 0-.55-.23-1.06-.59-1.42zM5.5 8.25C4.81 8.25 4.25 7.69 4.25 7s.56-1.25 1.25-1.25S6.75 6.31 6.75 7s-.56 1.25-1.25 1.25z" />
                                </svg> Labels</button>
                            <button class="sidebar-btn" onclick="openChecklistModal()"
                                style="width:auto; margin:0;"><svg viewBox="0 0 24 24" fill="currentColor">
                                    <path
                                        d="M19 3H5c-1.11 0-2 .89-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-9 14l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                                </svg> Checklist</button>
                            <button class="sidebar-btn" onclick="document.getElementById('attachmentFileInput').click()"
                                style="width:auto; margin:0;"><svg viewBox="0 0 24 24" fill="currentColor">
                                    <path
                                        d="M16.5 6v11.5c0 2.21-1.79 4-4 4s-4-1.79-4-4V5c0-1.38 1.12-2.5 2.5-2.5s2.5 1.12 2.5 2.5v10.5c0 .55-.45 1-1 1s-1-.45-1-1V6H10v9.5c0 1.38 1.12 2.5 2.5 2.5s2.5-1.12 2.5-2.5V5c0-2.21-1.79-4-4-4S7 2.79 7 5v12.5c0 3.04 2.46 5.5 5.5 5.5s5.5-2.46 5.5-5.5V6h-1.5z" />
                                </svg> Attachment</button>
                            <button class="sidebar-btn" onclick="openDatesModal()" style="width:auto; margin:0;"><svg
                                    viewBox="0 0 24 24" fill="currentColor">
                                    <path
                                        d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zM9 14H7v-2h2v2zm4 0h-2v-2h2v2zm4 0h-2v-2h2v2zm-8 4H7v-2h2v2zm4 0h-2v-2h2v2zm4 0h-2v-2h2v2z" />
                                </svg> Dates</button>
                        </div>
                        <!-- Metadata Grid -->
                        <div class="card-metadata-grid"
                            style="display: flex; gap: 24px; flex-wrap: wrap; margin-bottom: 32px; padding-left: 36px;">
                            @if(count($card->members) > 0)
                                <div class="metadata-item">
                                    <h3
                                        style="font-size: 12px; font-weight: 600; color: #9fadbc; text-transform: uppercase; margin-bottom: 8px;">
                                        Members</h3>
                                    <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                                        @foreach($card->members as $member)
                                            <div class="member-avatar"
                                                style="width:32px; height:32px; border-radius:50%; background:#0052cc; color:#fff; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:600; border: 2px solid #22272b;">
                                                {{ strtoupper(substr($member->name, 0, 2)) }}
                                            </div>
                                        @endforeach
                                        <div onclick="openMembersModal()"
                                            style="width:32px; height:32px; border-radius:50%; background:#3d444d; color:#9fadbc; display:flex; align-items:center; justify-content:center; font-size:18px; cursor:pointer;">
                                            +</div>
                                    </div>
                                </div>
                            @endif


                            <div class="metadata-item" id="labelsMetadataItem"
                                style="{{ count($cardLabels) > 0 ? '' : 'display:none;' }}">
                                <h3
                                    style="font-size: 12px; font-weight: 600; color: #9fadbc; text-transform: uppercase; margin-bottom: 8px;">
                                    Labels</h3>
                                <div id="cardLabelsList" style="display: flex; gap: 6px; flex-wrap: wrap;">
                                    @foreach($cardLabels as $label)
                                        @php
                                            $labelColor = is_string($label) ? ($labelColorsMap[$label] ?? '') : ($label->color ?? '');
                                            $labelName = is_string($label) ? ucfirst($label) : ($label->name ?? '');
                                        @endphp
                                        @if($labelColor)
                                            <div onclick="openLabelsModal()"
                                                style="background:{{ $labelColor }}; color:#fff; min-width:48px; height:32px; padding:0 12px; border-radius:4px; font-size:14px; font-weight:600; display:flex; align-items:center; justify-content:center; cursor:pointer;">
                                                {{ $labelName }}
                                            </div>
                                        @endif
                                    @endforeach
                                    <div onclick="openLabelsModal()"
                                        style="width:32px; height:32px; border-radius:4px; background:#3d444d; color:#9fadbc; display:flex; align-items:center; justify-content:center; font-size:18px; cursor:pointer;">
                                        +</div>
                                </div>
                            </div>
                            



                            <!-- Updated Date Format -->@if($card->start_date || $card->due_date)
                                <div class="metadata-item" style="margin-bottom: 20px;">
                                    <h3
                                        style="font-size: 12px; font-weight: 600; color: #9fadbc; text-transform: uppercase; margin-bottom: 8px;">
                                        Dates</h3>

                                    <div onclick="openDatesModal()"
                                        style="display: inline-flex; align-items: center; gap: 8px; background: #3d444d; padding: 6px 12px; border-radius: 3px; cursor: pointer; color: #b6c2cf; font-size: 14px; transition: background 0.2s;">

                                        @if($card->due_date)
                                            <input type="checkbox" {{ $card->is_completed ? 'checked' : '' }}
                                                onclick="event.stopPropagation(); toggleCardCompletion(this.checked)"
                                                style="width: 16px; height: 16px; cursor: pointer; margin: 0;">
                                        @endif

                                        <span style="display: flex; align-items: center; gap: 4px;">
                                            @if($card->start_date && $card->due_date)
                                                {{ \Carbon\Carbon::parse($card->start_date)->format('d M') }} -
                                                {{ \Carbon\Carbon::parse($card->due_date)->format('d M, H:i') }}
                                            @elseif($card->start_date)
                                                {{ \Carbon\Carbon::parse($card->start_date)->format('d M') }}
                                            @else
                                                {{ \Carbon\Carbon::parse($card->due_date)->format('d M, H:i') }}
                                            @endif

                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path d="m6 9 6 6 6-6" />
                                            </svg>
                                        </span>

                                        @if($card->is_completed)
                                            <span
                                                style="background:#1f845a; color:white; padding:2px 6px; border-radius:2px; font-size:11px; margin-left: 4px; font-weight:600;">Completed</span>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <div class="modal-overlay" id="datesModal" onclick="closeDatesModal()"
                                style="z-index: 4000;">
                                <div class="modal" onclick="event.stopPropagation()"
                                    style="background: #282e33; width: 320px; border-radius: 8px; padding: 12px; color: #b6c2cf; box-shadow: 0 8px 16px rgba(0,0,0,0.5);">
                                    <div class="modal-header"
                                        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                                        <div class="modal-title"
                                            style="font-weight: 600; font-size: 14px; text-align: center; flex-grow: 1;">
                                            Dates</div>
                                        <button class="modal-close" onclick="closeDatesModal()"
                                            style="background: none; border: none; color: #9fadbc; font-size: 20px; cursor: pointer;">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <div style="margin-bottom: 16px;">
                                            <label
                                                style="display: block; font-size: 12px; font-weight: 600; color: #9fadbc; margin-bottom: 8px;">Start
                                                date</label>
                                            <input type="date" id="startDateInput"
                                                value="{{ $card->start_date ? \Carbon\Carbon::parse($card->start_date)->format('Y-m-d') : '' }}"
                                                style="width: 100%; padding: 8px; background: #22272b; border: 1px solid #38414a; border-radius: 4px; color: #b6c2cf;">
                                        </div>
                                        <div style="margin-bottom: 16px;">
                                            <label
                                                style="display: block; font-size: 12px; font-weight: 600; color: #9fadbc; margin-bottom: 8px;">Due
                                                date</label>
                                            <input type="date" id="dueDateInput"
                                                value="{{ $card->due_date ? \Carbon\Carbon::parse($card->due_date)->format('Y-m-d') : '' }}"
                                                style="width: 100%; padding: 8px; background: #22272b; border: 1px solid #38414a; border-radius: 4px; color: #b6c2cf;">
                                        </div>
                                        <button onclick="saveDates()"
                                            style="width: 100%; padding: 10px; background: #0c66e4; color: white; border: none; border-radius: 4px; font-size: 14px; font-weight: 600; cursor: pointer; margin-bottom: 8px;">Save</button>
                                        <button onclick="removeDates()"
                                            style="width: 100%; padding: 10px; background: transparent; color: #9fadbc; border: 1px solid #38414a; border-radius: 4px; font-size: 14px; font-weight: 500; cursor: pointer;">Remove</button>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Action Buttons -->

                        <!-- Description Section -->
                        <div class="description-section" style="margin-bottom: 32px;">
                            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                                <svg viewBox="0 0 24 24" fill="currentColor"
                                    style="width: 20px; height: 20px; color: #9fadbc;">
                                    <path d="M14 17H4v2h10v-2zm6-8H4v2h16V9zM4 15h16v-2H4v2zM4 5v2h16V5H4z" />
                                </svg>
                                <h3 style="font-size: 16px; font-weight: 600; color: #b6c2cf; margin: 0;">Description
                                </h3>
                                <button class="btn-cancel" onclick="editDescription()"
                                    style="background:#3d444d; color:#b6c2cf; border:none; padding:4px 12px; border-radius:3px; cursor:pointer; font-size:13px; margin-left: auto;">Edit</button>
                            </div>
                            <div id="descriptionDisplay" style="padding-left: 36px;">
                                <div id="descriptionText"
                                    class="description-content {{ empty(trim(strip_tags($card->description))) ? 'empty' : '' }}"
                                    onclick="editDescription()">
                                    {!! $card->description ?: 'Add a more detailed description...' !!}
                                </div>
                            </div>
                            <div id="descriptionEditor" style="display: none; padding-left: 36px;">
                                <div class="description-editor-wrapper">
                                    <div id="quillDescriptionEditor">{!! $card->description ?? '' !!}</div>
                                </div>
                                <div style="margin-top: 8px; display: flex; gap: 8px;">
                                    <button class="btn-save" onclick="saveDescription()">Save</button>
                                    <button class="btn-cancel" onclick="cancelDescriptionEdit()">Cancel</button>
                                </div>
                            </div>
                        </div>

                        <!-- Checklist Section -->
                        <div class="checklist-section" id="checklistSection"
                            style="margin-bottom: 32px; {{ $card->checklistItems->count() === 0 ? 'display:none;' : '' }}">
                            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                                <svg viewBox="0 0 24 24" fill="currentColor"
                                    style="width: 20px; height: 20px; color: #9fadbc;">
                                    <path
                                        d="M19 3H5c-1.11 0-2 .89-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-9 14l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                                </svg>
                                <h3 style="font-size: 16px; font-weight: 600; color: #b6c2cf; margin: 0;">Checklist</h3>
                                <button class="btn-cancel" onclick="deleteChecklist()"
                                    style="margin-left: auto;">Delete</button>
                            </div>
                            @php
                                $total = $card->checklistItems->count();
                                $completed = $card->checklistItems->where('is_completed', true)->count();
                                $percent = $total > 0 ? round(($completed / $total) * 100) : 0;
                            @endphp
                            <div style="padding-left: 36px;">
                                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                                    <span style="font-size: 12px; color: #9fadbc; width: 32px;"
                                        id="checklistPercent">{{ $percent }}%</span>
                                    <div
                                        style="flex:1; height:8px; background:#3d444d; border-radius:4px; overflow:hidden;">
                                        <div id="checklistProgressBar"
                                            style="width:{{ $percent }}%; height:100%; background:#579dff; transition:width 0.2s;">
                                        </div>
                                    </div>
                                </div>
                                <div id="checklistItems">
                                    @foreach($card->checklistItems as $item)
                                        <div class="checklist-item" data-item-id="{{ $item->id }}"
                                            style="display:flex; align-items:flex-start; gap:12px; margin-bottom:8px;">
                                            <input type="checkbox"
                                                onchange="toggleChecklistItem({{ $item->id }}, this.checked)" {{ $item->is_completed ? 'checked' : '' }} style="margin-top:3px;">
                                            <label
                                                style="flex:1; font-size:14px; color:#b6c2cf; {{ $item->is_completed ? 'text-decoration:line-through; opacity:0.6;' : '' }}">{{ $item->title }}</label>
                                            <button onclick="deleteChecklistItem({{ $item->id }})"
                                                style="background:none; border:none; color:#9fadbc; cursor:pointer;">×</button>
                                        </div>
                                    @endforeach
                                </div>
                                <input type="text" id="newChecklistItem" placeholder="Add an item"
                                    onkeypress="if(event.key==='Enter') addChecklistItem()"
                                    style="width: 100%; background: #22272b; border: 1px solid #38414a; border-radius: 4px; padding: 8px 12px; color: #b6c2cf; font-size: 14px; margin-bottom: 8px;">
                                <button class="btn-save" onclick="addChecklistItem()">Add</button>
                            </div>
                        </div>

                        <!-- Attachments Section -->
                        @if($card->attachments->count() > 0)
                            <div class="attachments-section" style="margin-bottom: 32px;">
                                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                                    <svg viewBox="0 0 24 24" fill="currentColor"
                                        style="width: 20px; height: 20px; color: #9fadbc;">
                                        <path
                                            d="M16.5 6v11.5c0 2.21-1.79 4-4 4s-4-1.79-4-4V5c0-1.38 1.12-2.5 2.5-2.5s2.5 1.12 2.5 2.5v10.5c0 .55-.45 1-1 1s-1-.45-1-1V6H10v9.5c0 1.38 1.12 2.5 2.5 2.5s2.5-1.12 2.5-2.5V5c0-2.21-1.79-4-4-4S7 2.79 7 5v12.5c0 3.04 2.46 5.5 5.5 5.5s5.5-2.46 5.5-5.5V6h-1.5z" />
                                    </svg>
                                    <h3 style="font-size: 16px; font-weight: 600; color: #b6c2cf; margin: 0;">Attachments
                                    </h3>
                                    <button class="btn-cancel"
                                        onclick="document.getElementById('attachmentFileInput').click()"
                                        style="margin-left: auto;">Add</button>
                                </div>
                                <div style="padding-left: 36px;" id="attachmentsList">
                                    @foreach($card->attachments as $attachment)
                                        @php
                                            $cleanPath = str_replace(['/storage/', 'storage/', 'http://127.0.0.1:8000/storage/', 'https://127.0.0.1/storage/'], '', $attachment->file_path ?? '');
                                            $fileUrl = $cleanPath ? Storage::url($cleanPath) : '';
                                            $isImage = !empty($attachment->name) && preg_match('/\.(jpg|jpeg|png|gif|webp|svg)$/i', $attachment->name);
                                            $extension = !empty($attachment->name) ? strtoupper(pathinfo($attachment->name, PATHINFO_EXTENSION)) : '';
                                        @endphp
                                        <div class="attachment-item" data-attachment-id="{{ $attachment->id }}"
                                            style="display: flex; gap: 16px; margin-bottom: 12px; padding: 8px; border-radius: 6px; cursor: pointer;"
                                            onmouseover="this.style.background='#2c333a'"
                                            onmouseout="this.style.background='transparent'">

                                            @if($isImage)
                                                <div onclick="previewImage('{{ $fileUrl }}', '{{ $attachment->name }}')"
                                                    style="width: 112px; height: 80px; background: #3d444d; border-radius: 3px; background-image: url('{{ $fileUrl }}'); background-size: cover; background-position: center; flex-shrink: 0;">
                                                </div>
                                            @else
                                                <div style="width: 112px; height: 80px; background: #3d444d; border-radius: 3px; display: flex; align-items: center; justify-content: center; font-size: 18px; color: #9fadbc; font-weight: bold; flex-shrink: 0;"
                                                    title="{{ $attachment->name }}">
                                                    {{ $extension ?: 'FILE' }}
                                                </div>
                                            @endif

                                            <div
                                                style="flex: 1; min-width: 0; display: flex; flex-direction: column; justify-content: center;">
                                                <div
                                                    style="font-size: 14px; font-weight: 700; color: #b6c2cf; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                    {{ $attachment->name }}
                                                </div>
                                                <div style="font-size: 12px; color: #9fadbc; margin: 4px 0 8px;">Added
                                                    {{ $attachment->created_at->diffForHumans() }}
                                                </div>
                                                <div style="display: flex; gap: 12px; font-size: 13px; margin-top: 4px;">
                                                    @if(!$isImage)
                                                        <a href="{{ $fileUrl }}" download="{{ $attachment->name }}"
                                                            style="color: #9fadbc; text-decoration: underline; cursor: pointer; transition: color 0.1s;"
                                                            onmouseover="this.style.color='#b6c2cf'"
                                                            onmouseout="this.style.color='#9fadbc'">Download</a>
                                                    @endif
                                                    <span
                                                        style="color: #9fadbc; text-decoration: underline; cursor: pointer; transition: color 0.1s;"
                                                        onmouseover="this.style.color='#b6c2cf'"
                                                        onmouseout="this.style.color='#9fadbc'"
                                                        onclick="deleteAttachment({{ $attachment->id }})">Delete</span>
                                                    @if($isImage)
                                                        <span
                                                            style="color: #9fadbc; text-decoration: underline; cursor: pointer; transition: color 0.1s;"
                                                            onmouseover="this.style.color='#b6c2cf'"
                                                            onmouseout="this.style.color='#9fadbc'"
                                                            onclick="makeAttachmentCover('{{ $fileUrl }}')">{{ (str_contains($card->cover ?? '', $cleanPath)) ? 'Remove cover' : 'Make cover' }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div> <!-- End card-view-main -->

                    <!-- Right Column (Activity/Sidebar) -->
                    <div class="card-view-sidebar"
                        style="width: 515px; min-width: 0; flex-shrink: 0; padding: 24px 20px; background: #22272b; display: flex; flex-direction: column; overflow-y: auto; overflow-x: hidden; border-left: 1px solid #38414a;">
                        @php
                            $currentUserMember = $workspaceMembers->firstWhere('id', auth()->id());
                            $userPivot = $currentUserMember ? $currentUserMember->pivot : null;
                            $hasCommentAccess = auth()->user()->isSystemAdmin()
                                || $board->workspace->isOwner(auth()->id())
                                || $board->sharedUsers()->where('user_id', auth()->id())->exists();
                        @endphp

                        <div
                            style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <svg viewBox="0 0 24 24" fill="currentColor"
                                    style="width: 20px; height: 20px; color: #9fadbc;">
                                    <path d="M4 6h16v12H4V6zm2 2v8h12V8H6zm2 2h8v2H8v-2zm0 4h5v2H8v-2z" />
                                </svg>
                                <h3 style="font-size: 16px; font-weight: 600; color: #b6c2cf; margin: 0;">Activity</h3>
                            </div>
                            <button class="btn-cancel" onclick="toggleDetails()" id="toggleDetailsBtn">Show
                                details</button>
                        </div>

                        <div style="margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid #38414a;">
                            @if($hasCommentAccess)
                                <div style="display: flex; gap: 12px;">
                                    <div
                                        style="width: 32px; height: 32px; border-radius: 50%; background: #0052cc; color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 12px; flex-shrink: 0; margin-top: 2px;">
                                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                                    </div>
                                    <div style="flex: 1; min-width: 0;">
                                        <div class="comment-editor-wrapper">
                                            <div id="quillCommentEditor" style="min-height: 40px; cursor: text; "></div>
                                        </div>
                                        <div id="commentActions" style="margin-top: 8px; display: none;">
                                            <button class="btn-save" onclick="addComment()">Save</button>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div
                                    style="background: #1f2328; padding: 12px; border-radius: 4px; color: #9fadbc; font-size: 13px;">
                                    You don't have permission to comment on this card.</div>
                            @endif
                        </div>

                        <div id="activityList"
                            style="flex: 1; overflow-y: auto; max-height: 500px; padding-right: 8px;">
                            <!-- Activities and comments loaded via JS -->
                        </div>


                    </div> <!-- End card-view-sidebar -->
                </div> <!-- End card-view-inner -->
            </div> <!-- End card-view-content -->
        </div> <!-- End card-view-container -->
    </div> <!-- End card-view-overlay -->

    <!-- List Dropdown Menu -->
    <div class="dropdown-menu" id="listDropdown" style="display: none;">
        @foreach($board->lists as $boardList)
            <div class="dropdown-item" onclick="moveToList({{ $boardList->id }})">
                {{ $boardList->name }}
            </div>
        @endforeach
    </div>



    <!-- More Options Menu moved inline with button above -->

    <!-- Labels Modal -->
    <div class="modal-overlay" id="labelsModal" onclick="closeLabelsModal()">
        <div class="modal" onclick="event.stopPropagation()"
            style="max-width: 304px; border-radius: 8px; padding: 0; background: #282e33; border: 1px solid #3d444d;">
            <div class="modal-header"
                style="border-bottom: 1px solid #38414a; padding: 8px 12px; position: relative; display: flex; align-items: center; justify-content: center;">
                <div class="modal-title" style="color: #b6c2cf; font-size: 14px; font-weight: 600;">Labels</div>
                <button class="modal-close" onclick="closeLabelsModal()"
                    style="color: #9fadbc; position: absolute; right: 8px; font-size: 20px; background: none; border: none; cursor: pointer; width: 32px; height: 32px; border-radius: 4px; display: flex; align-items: center; justify-content: center; transition: background 0.2s;">×</button>
            </div>
            <div class="modal-body" style="padding: 12px;">
                <!-- Search Input -->
                <div style="margin-bottom: 16px;">
                    <input type="text" id="labelSearchInput" onkeyup="filterLabels()" placeholder="Search labels..."
                        style="width: 100%; background: #22272b; border: 2px solid #3d444d; border-radius: 4px; padding: 8px 12px; color: #b6c2cf; font-size: 14px; outline: none; transition: border-color 0.2s;">
                </div>

                <div
                    style="font-size: 11px; font-weight: 700; color: #9fadbc; text-transform: uppercase; margin-bottom: 8px;">
                    Labels</div>

                <div id="labelOptions" style="margin-bottom: 16px; max-height: 400px; overflow-y: auto;">
                    @php
                        // Get current label IDs - labels are stored as array of IDs
                        $currentLabelIds = $card->labels ?? [];
                        // Ensure it's an array
                        if (!is_array($currentLabelIds)) {
                            $currentLabelIds = [];
                        }
                    @endphp
                    @foreach($boardLabels as $label)
                        <div class="label-list-item" data-id="{{ $label->id }}" data-color="{{ $label->color }}"
                            data-name="{{ $label->name }}"
                            style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                            <input type="checkbox" {{ in_array($label->id, $currentLabelIds) ? 'checked' : '' }}
                                onchange="toggleLabelCheckbox(this)"
                                style="width: 16px; height: 16px; cursor: pointer; accent-color: #579dff;">

                            <div class="label-full-bar" onclick="toggleLabelBar(this)"
                                style="flex: 1; height: 32px; background:{{ $label->color }}; color: {{ $label->color == '#f2d600' ? '#172b4d' : '#fff' }}; border-radius: 4px; padding: 0 12px; display: flex; align-items: center; font-size: 14px; font-weight: 600; cursor: pointer; min-width: 0;">
                                <span
                                    style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $label->name }}</span>
                            </div>

                            <button class="label-list-edit" title="Edit label"
                                onclick="event.stopPropagation(); openEditLabelModal({{ $label->id }}, '{{ addslashes($label->name) }}', '{{ $label->color }}')"
                                style="background: none; border: none; color: #9fadbc; cursor: pointer; font-size: 16px; padding: 4px; border-radius: 3px; display: flex; align-items: center; justify-content: center; transition: background 0.2s;">
                                ✏️
                            </button>
                        </div>
                    @endforeach
                </div>

                <button type="button" class="btn-create-label" onclick="openCreateLabelModal()"
                    style="width: 100%; background: #ffffff1a; color: #b6c2cf; border: none; padding: 10px; border-radius: 4px; font-size: 14px; cursor: pointer; font-weight: 500; transition: background 0.2s; margin-top: 8px;">
                    Create a new label
                </button>
                <button type="button" class="btn-save" style="margin-top:8px; width: 100%; display: none;"
                    onclick="saveLabels()">Save</button>
            </div>
        </div>
    </div>

    <!-- Edit/Create Label Modal -->
    <div class="modal-overlay" id="editLabelModal" onclick="closeEditLabelModal()">
        <div class="modal" onclick="event.stopPropagation()"
            style="background: #282e33; max-width: 304px; border-radius: 8px;">
            <div class="modal-header"
                style="border-bottom: 1px solid #38414a; background: #282e33; padding: 8px 12px; position: relative; display: flex; align-items: center; justify-content: center;">
                <!-- <button class="modal-back-btn" onclick="closeEditLabelModal()"
                    style="position: absolute; left: 8px; background: none; border: none; color: #9fadbc; cursor: pointer; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 4px; font-size: 18px; transition: background 0.2s;">
                    〈
                </button> -->
                <div class="modal-title" id="editLabelTitle" style="color: #b6c2cf; font-size: 14px; font-weight: 600;">
                    Edit label</div>
                <button class="modal-close" onclick="closeEditLabelModal()"
                    style="color: #9fadbc; position: absolute; right: 8px; font-size: 20px; background: none; border: none; cursor: pointer; width: 32px; height: 32px; border-radius: 4px; display: flex; align-items: center; justify-content: center; transition: background 0.2s;">×</button>
            </div>
            <div class="modal-body" style="background: #282e33; padding: 12px;">
                <style>
                    .modal-back-btn:hover {
                        background: #3d444d;
                    }

                    #editLabelModal .modal-close:hover {
                        background: #3d444d;
                    }

                    .label-preview {
                        height: 32px;
                        border-radius: 3px;
                        display: flex;
                        align-items: center;
                        padding: 0 12px;
                        font-size: 14px;
                        font-weight: 700;
                        margin-bottom: 16px;
                    }

                    .edit-label-input-label {
                        font-size: 12px;
                        font-weight: 600;
                        color: #9fadbc;
                        margin-bottom: 4px;
                        display: block;
                    }

                    .edit-label-name-input {
                        width: 100%;
                        background: #22272b;
                        border: 2px solid #3d444d;
                        border-radius: 3px;
                        padding: 8px 12px;
                        color: #b6c2cf;
                        font-size: 14px;
                        margin-bottom: 16px;
                        box-sizing: border-box;
                    }

                    .edit-label-name-input:focus {
                        outline: none;
                        border-color: #579dff;
                        background: #1d2125;
                    }

                    .color-grid {
                        display: grid;
                        grid-template-columns: repeat(5, 1fr);
                        gap: 8px;
                        margin-bottom: 16px;
                    }

                    .color-box {
                        aspect-ratio: 1;
                        border-radius: 3px;
                        cursor: pointer;
                        border: 2px solid transparent;
                        transition: all 0.1s;
                        position: relative;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    }

                    .color-box:hover {
                        transform: scale(1.05);
                        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
                    }

                    .color-box.selected {
                        border-color: #0079bf;
                        box-shadow: 0 0 0 2px #0079bf;
                    }

                    .color-box.selected::after {
                        content: '✓';
                        color: white;
                        font-size: 18px;
                        font-weight: bold;
                        text-shadow: 0 0 2px rgba(0, 0, 0, 0.5);
                    }

                    .edit-label-actions {
                        display: flex;
                        gap: 8px;
                    }

                    .btn-save-label {
                        flex: 1;
                        background: #0079bf;
                        color: #ffffff;
                        border: none;
                        padding: 8px 12px;
                        border-radius: 3px;
                        font-size: 14px;
                        font-weight: 500;
                        cursor: pointer;
                    }

                    .btn-save-label:hover {
                        background: #026aa7;
                    }

                    .btn-delete-label {
                        background: #eb5a46;
                        color: #ffffff;
                        border: none;
                        padding: 8px 12px;
                        border-radius: 3px;
                        font-size: 14px;
                        font-weight: 500;
                        cursor: pointer;
                    }

                    .btn-delete-label:hover {
                        background: #cf513d;
                    }
                </style>

                <div class="label-preview" id="labelPreview" style="background: #0052cc; color: #ffffff;">
                    <span id="labelPreviewText">Label preview</span>
                </div>

                <label class="edit-label-input-label">Title</label>
                <input type="text" class="edit-label-name-input" id="editLabelNameInput"
                    placeholder="Enter label name..." oninput="updateLabelPreview()">

                <label class="edit-label-input-label">Select a color</label>
                <div class="color-grid" id="colorGrid" style="margin-bottom: 8px;">
                    <!-- Colors will be populated by JavaScript -->
                </div>

                <button type="button" class="remove-color-btn" onclick="removeLabelColor()"
                    style="width: 100%; background: #38414a; color: #b6c2cf; border: none; padding: 10px; border-radius: 4px; font-size: 14px; cursor: pointer; margin-bottom: 16px; display: flex; align-items: center; justify-content: center; gap: 8px; transition: background 0.2s;">
                    ✕ Remove color
                </button>

                <div
                    style="border-top: 1px solid #3d444d; padding-top: 16px; display: flex; justify-content: space-between; align-items: center;">
                    <button class="btn-save-label" id="saveLabelBtn" onclick="saveLabelChanges()"
                        style="background: #579dff; color: #1d2125; border: none; padding: 8px 24px; border-radius: 3px; font-size: 14px; font-weight: 600; cursor: pointer;">Save</button>
                    <button class="btn-delete-label" id="deleteLabelBtn" onclick="deleteLabel()"
                        style="background: #f87168; color: #1d2125; border: none; padding: 8px 16px; border-radius: 3px; font-size: 14px; font-weight: 600; cursor: pointer; display: none;">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cover Modal -->
    <div class="modal-overlay" id="coverModal" onclick="closeCoverModal()">
        <div class="modal" onclick="event.stopPropagation()"
            style="max-width: 600px; max-height: 90vh; overflow-y: auto;">
            <div class="modal-header">
                <div class="modal-title">Cover</div>
                <button class="modal-close" onclick="closeCoverModal()">×</button>
            </div>
            <div class="modal-body">
                <!-- Colors Section -->
                <div style="margin-bottom: 20px;">
                    <div
                        style="font-size: 12px; font-weight: 600; color: #9fadbc; margin-bottom: 8px; text-transform: uppercase;">
                        Colors</div>
                    <div class="cover-colors-grid">
                        <div class="cover-color-option" data-color="#61bd4f" style="background:#61bd4f;"
                            onclick="selectCoverColor('#61bd4f', this)"></div>
                        <div class="cover-color-option" data-color="#f2d600" style="background:#f2d600;"
                            onclick="selectCoverColor('#f2d600', this)"></div>
                        <div class="cover-color-option" data-color="#ff9f1a" style="background:#ff9f1a;"
                            onclick="selectCoverColor('#ff9f1a', this)"></div>
                        <div class="cover-color-option" data-color="#eb5a46" style="background:#eb5a46;"
                            onclick="selectCoverColor('#eb5a46', this)"></div>
                        <div class="cover-color-option" data-color="#c377e0" style="background:#c377e0;"
                            onclick="selectCoverColor('#c377e0', this)"></div>
                        <div class="cover-color-option" data-color="#0079bf" style="background:#0079bf;"
                            onclick="selectCoverColor('#0079bf', this)"></div>
                        <div class="cover-color-option" data-color="#00c2e0" style="background:#00c2e0;"
                            onclick="selectCoverColor('#00c2e0', this)"></div>
                        <div class="cover-color-option" data-color="#51e898" style="background:#51e898;"
                            onclick="selectCoverColor('#51e898', this)"></div>
                        <div class="cover-color-option" data-color="#ff78cb" style="background:#ff78cb;"
                            onclick="selectCoverColor('#ff78cb', this)"></div>
                        <div class="cover-color-option" data-color="#b3b3b3" style="background:#b3b3b3;"
                            onclick="selectCoverColor('#b3b3b3', this)"></div>
                    </div>
                </div>

                <!-- Upload Image Section -->
                <div style="margin-bottom: 20px;">
                    <div
                        style="font-size: 12px; font-weight: 600; color: #9fadbc; margin-bottom: 8px; text-transform: uppercase;">
                        Upload</div>
                    <input type="file" id="coverImageUpload" accept="image/*" style="display: none;"
                        onchange="uploadCoverImage(event)">
                    <button type="button" onclick="document.getElementById('coverImageUpload').click()"
                        style="width: 100%; background: #38414a; color: #b6c2cf; border: none; padding: 12px; border-radius: 4px; cursor: pointer; font-size: 14px; transition: background 0.2s;">
                        📤 Upload a cover image
                    </button>
                    <div style="font-size: 11px; color: #9fadbc; margin-top: 6px; text-align: center;">Tip: Drag an
                        image on to the card to upload it</div>
                </div>

                <!-- Attachments Section -->
                <div id="coverAttachmentsSection" style="margin-bottom: 20px; display: none;">
                    <div
                        style="font-size: 12px; font-weight: 600; color: #9fadbc; margin-bottom: 8px; text-transform: uppercase;">
                        Attachments</div>
                    <div id="coverAttachmentsGrid" class="cover-colors-grid"></div>
                </div>

                <!-- Sample Images Section (Unsplash-style) -->
                <div style="margin-bottom: 20px;">
                    <div
                        style="font-size: 12px; font-weight: 600; color: #9fadbc; margin-bottom: 8px; text-transform: uppercase;">
                        Photos from Unsplash</div>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-bottom: 8px;">
                        <div class="unsplash-image"
                            onclick="selectCoverColor('https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800', this)"
                            style="height: 80px; background: url('https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400') center/cover; border-radius: 4px; cursor: pointer; border: 2px solid transparent; transition: border 0.2s;">
                        </div>
                        <div class="unsplash-image"
                            onclick="selectCoverColor('https://images.unsplash.com/photo-1519681393784-d120267933ba?w=800', this)"
                            style="height: 80px; background: url('https://images.unsplash.com/photo-1519681393784-d120267933ba?w=400') center/cover; border-radius: 4px; cursor: pointer; border: 2px solid transparent; transition: border 0.2s;">
                        </div>
                        <div class="unsplash-image"
                            onclick="selectCoverColor('https://images.unsplash.com/photo-1501594907352-04cda38ebc29?w=800', this)"
                            style="height: 80px; background: url('https://images.unsplash.com/photo-1501594907352-04cda38ebc29?w=400') center/cover; border-radius: 4px; cursor: pointer; border: 2px solid transparent; transition: border 0.2s;">
                        </div>
                        <div class="unsplash-image"
                            onclick="selectCoverColor('https://images.unsplash.com/photo-1511593358241-7eea1f3c84e5?w=800', this)"
                            style="height: 80px; background: url('https://images.unsplash.com/photo-1511593358241-7eea1f3c84e5?w=400') center/cover; border-radius: 4px; cursor: pointer; border: 2px solid transparent; transition: border 0.2s;">
                        </div>
                        <div class="unsplash-image"
                            onclick="selectCoverColor('https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800', this)"
                            style="height: 80px; background: url('https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400') center/cover; border-radius: 4px; cursor: pointer; border: 2px solid transparent; transition: border 0.2s;">
                        </div>
                        <div class="unsplash-image"
                            onclick="selectCoverColor('https://images.unsplash.com/photo-1480714378408-67cf0d13bc1b?w=800', this)"
                            style="height: 80px; background: url('https://images.unsplash.com/photo-1480714378408-67cf0d13bc1b?w=400') center/cover; border-radius: 4px; cursor: pointer; border: 2px solid transparent; transition: border 0.2s;">
                        </div>
                    </div>
                    <input type="text" placeholder="Search for photos"
                        style="width: 100%; background: #2c333a; border: 1px solid #38414a; color: #b6c2cf; padding: 10px; border-radius: 4px; font-size: 14px;"
                        readonly onclick="showToast('Unsplash search coming soon!', 'info')">
                </div>

                <button type="button" onclick="saveCover()"
                    style="width:100%; background:#0c66e4; color:#fff; border:none; padding:10px 16px; border-radius:4px; font-size:14px; font-weight:600; cursor:pointer; margin-bottom:8px; transition:background 0.2s;"
                    onmouseover="this.style.background='#0055cc'"
                    onmouseout="this.style.background='#0c66e4'">Save</button>
                <button type="button" onclick="removeCover()"
                    style="width:100%; background:#3d444d; color:#b6c2cf; border:none; padding:10px 16px; border-radius:4px; font-size:14px; font-weight:500; cursor:pointer; transition:background 0.2s;"
                    onmouseover="this.style.background='#454f59'" onmouseout="this.style.background='#3d444d'">Remove
                    cover</button>
            </div>
        </div>
    </div>

    <!-- Move Modal -->
    <div class="modal-overlay" id="moveModal" onclick="closeMoveModal()">
        <div class="modal" onclick="event.stopPropagation()">
            <div class="modal-header">
                <div class="modal-title">Move card</div>
                <button class="modal-close" onclick="closeMoveModal()">×</button>
            </div>
            <div class="modal-body">
                <label style="display: block; margin-bottom: 8px; color: #b6c2cf; font-size: 14px;">Select list:</label>
                <select class="list-select" id="moveListSelect">
                    @foreach($board->lists as $boardList)
                        <option value="{{ $boardList->id }}" {{ $boardList->id == $list->id ? 'selected' : '' }}>
                            {{ $boardList->name }}</option>
                    @endforeach
                </select>
                <button type="button" class="btn-save" style="width: 100%;" onclick="saveMove()">Move</button>
            </div>
        </div>
    </div>

    <!-- Members Modal -->
    <div class="modal-overlay" id="membersModal" onclick="closeMembersModal()">
        <div class="modal" onclick="event.stopPropagation()" style="width: 300px; padding: 0;">
            <div class="modal-header" style="justify-content: center; position: relative; padding: 12px 20px;">
                <div class="modal-title" style="font-size: 14px; font-weight: 600; color: #b6c2cf;">Members</div>
                <button class="modal-close" onclick="closeMembersModal()"
                    style="position: absolute; right: 12px; top: 12px; width: 24px; height: 24px; font-size: 16px;">×</button>
            </div>
            <div class="modal-body" style="padding: 12px;">
                <div style="position: relative;">
                    <input type="text" id="memberSearchInput" oninput="searchMembers(this.value)"
                        placeholder="Search members..."
                        style="width:100%; background:#22272b; border:1px solid #738496; border-radius:4px; padding:8px 12px; color:#b6c2cf; font-size:14px; font-family:inherit; box-sizing:border-box;">
                    <div id="memberSearchResults"
                        style="display:none; margin-top:4px; background:#282e33; border:1px solid #38414a; border-radius:4px; overflow:hidden;">
                    </div>
                </div>

                <!-- Current card members -->
                <div id="currentMembersList" style="margin-top:12px;">
                    @foreach($card->members as $member)
                        <div class="ms-item" data-user-id="{{ $member->id }}" data-name="{{ strtolower($member->name) }}"
                            style="display:flex; align-items:center; gap:10px; padding:6px 8px; border-radius:4px; cursor:pointer; transition:background 0.15s;"
                            onmouseover="this.style.background='#2c333a'" onmouseout="this.style.background='transparent'"
                            onclick="toggleCardMember({{ $member->id }}, '{{ $member->name }}', true, this)">
                            <div
                                style="width:32px;height:32px;border-radius:50%;background:#0052cc;color:#fff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:600;flex-shrink:0;">
                                {{ strtoupper(substr($member->name, 0, 2)) }}
                            </div>
                            <span style="flex:1;font-size:14px;color:#b6c2cf;">{{ $member->name }}</span>
                            <span style="color:#579dff;font-size:16px;font-weight:bold;">✓</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>



    <!-- Image Preview Modal -->
    <div class="modal-overlay" id="imagePreviewModal" onclick="closeImagePreview()">
        <div class="image-preview-modal" onclick="event.stopPropagation()">
            <div class="modal-header">
                <div class="modal-title" id="imagePreviewTitle">Image</div>
                <button class="modal-close" onclick="closeImagePreview()">×</button>
            </div>
            <div class="image-preview-container">
                <img id="imagePreviewImg" src="" alt="Preview">
            </div>
            <div class="image-preview-info">
                <div class="image-preview-name" id="imagePreviewName"></div>
                <div class="image-preview-actions">
                    <a id="imagePreviewDownload" href="" download target="_blank">Download</a>
                    <button onclick="closeImagePreview()">Close</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        const boardId = {{ $board->id }};
        const listId = {{ $list->id }};
        const cardId = {{ $card->id }};
        const boardLabels = @json($boardLabels);
        const currentLabels = @json($cardLabels);
        let selectedLabels = [...currentLabels];
        let selectedCoverColor = null;
        const isOwnerOrAdmin = {{ (auth()->user()->isSystemAdmin() || $board->workspace->isOwner(auth()->id())) ? 'true' : 'false' }};


        // ===== @MENTION AUTOCOMPLETE =====
        let mentionBox = null;
        let focusedMentionIndex = -1;
        let currentMentionUsers = [];
        let mentionStartIndex = 0;
        let mentionStartLen = 0;

        function showMentionSuggestions(users, atIndex, atLen) {
            hideMentionSuggestions();
            if (!users.length) return;

            currentMentionUsers = users;
            mentionStartIndex = atIndex;
            mentionStartLen = atLen;
            focusedMentionIndex = 0; // Highlight first by default

            mentionBox = document.createElement('div');
            mentionBox.id = 'mentionBox';
            mentionBox.style.cssText = 'position:fixed;background:#22272b;border:1px solid #3c444d;border-radius:6px;min-width:200px;box-shadow:0 8px 24px rgba(0,0,0,0.5);z-index:9999;padding:4px 0;';

            const editorEl = document.getElementById('quillCommentEditor');
            const rect = editorEl.getBoundingClientRect();
            mentionBox.style.top = (rect.bottom + 4) + 'px';
            mentionBox.style.left = rect.left + 'px';

            renderMentionList();
            document.body.appendChild(mentionBox);
        }

        function renderMentionList() {
            if (!mentionBox) return;
            mentionBox.innerHTML = '';
            currentMentionUsers.forEach((u, index) => {
                const item = document.createElement('div');
                const isActive = index === focusedMentionIndex;
                item.className = 'mention-item' + (isActive ? ' active' : '');
                item.style.cssText = `padding:8px 14px;font-size:13px;color:#b6c2cf;cursor:pointer;display:flex;align-items:center;gap:10px;transition:background 0.2s;${isActive ? 'background:#0c66e4;color:#fff;' : ''}`;

                const initials = u.name.substring(0, 2).toUpperCase();
                item.innerHTML = `
                    <div style="width:28px;height:28px;border-radius:50%;background:${isActive ? 'rgba(255,255,255,0.2)' : '#0052cc'};color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;flex-shrink:0;">${initials}</div>
                    <span style="font-weight:${isActive ? '600' : '400'}">${u.name}</span>
                `;

                item.onclick = () => insertMention(u.name, mentionStartIndex, mentionStartLen);
                mentionBox.appendChild(item);
            });
        }

        function hideMentionSuggestions() {
            if (mentionBox) {
                mentionBox.remove();
                mentionBox = null;
                focusedMentionIndex = -1;
                currentMentionUsers = [];
            }
        }

        function insertMention(name, atIndex, atLen) {
            if (!window.commentQuill) return;
            const sel = window.commentQuill.getSelection();
            const currentIndex = sel ? sel.index : atIndex + atLen;
            const deleteLen = currentIndex - atIndex;

            window.commentQuill.deleteText(atIndex, deleteLen);
            window.commentQuill.insertText(atIndex, `@${name}`, { color: '#579dff', bold: true });
            window.commentQuill.insertText(atIndex + name.length + 1, ' ', { color: false, bold: false });
            window.commentQuill.setSelection(atIndex + name.length + 2);
            hideMentionSuggestions();
        }

        document.addEventListener('keydown', e => {
            if (mentionBox) {
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    focusedMentionIndex = (focusedMentionIndex + 1) % currentMentionUsers.length;
                    renderMentionList();
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    focusedMentionIndex = (focusedMentionIndex - 1 + currentMentionUsers.length) % currentMentionUsers.length;
                    renderMentionList();
                } else if (e.key === 'Enter') {
                    if (focusedMentionIndex >= 0) {
                        e.preventDefault();
                        insertMention(currentMentionUsers[focusedMentionIndex].name, mentionStartIndex, mentionStartLen);
                    }
                } else if (e.key === 'Escape') {
                    hideMentionSuggestions();
                }
            } else if (e.key === 'Escape') {
                hideMentionSuggestions();
            }
        });

        // Utility functions first to ensure they are defined
        function toggleLabelCheckbox(checkbox) {
            saveLabels();
        }

        function toggleLabelBar(bar) {
            const cb = bar.parentElement.querySelector('input[type=checkbox]');
            cb.checked = !cb.checked;
            toggleLabelCheckbox(cb);
        }

        function showToast(message, type = 'success') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: type,
                title: message,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                background: '#22272b',
                color: '#b6c2cf',
                customClass: { popup: 'swal2-toast-dark' }
            });
        }



        function openListDropdown() {
            const dropdown = document.getElementById('listDropdown');
            const isVisible = dropdown.style.display === 'block';
            document.querySelectorAll('.dropdown-menu').forEach(menu => menu.style.display = 'none');
            if (!isVisible) {
                dropdown.style.display = 'block';
                const listBtn = document.querySelector('.list-dropdown');
                const rect = listBtn.getBoundingClientRect();
                dropdown.style.position = 'fixed';
                dropdown.style.top = (rect.bottom + 4) + 'px';
                dropdown.style.left = rect.left + 'px';
            }
        }

        function moveToList(targetListId) {
            if (targetListId === listId) {
                document.getElementById('listDropdown').style.display = 'none';
                return;
            }

            fetch(`/boards/${boardId}/lists/${listId}/cards/${cardId}/move`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    target_list_id: targetListId,
                    position: 0
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = '{{ route('boards.show', $board) }}';
                    }
                })
                .catch(error => { });
        }

        function openMoreMenu() {
            const menu = document.getElementById('moreMenu');
            const isVisible = menu.style.display === 'block';
            document.querySelectorAll('[id="moreMenu"]').forEach(m => m.style.display = 'none');
            menu.style.display = isVisible ? 'none' : 'block';
        }

        function toggleAddMenu() {
            const dropdown = document.getElementById('addMenuDropdown');
            if (dropdown.style.display === 'none' || dropdown.style.display === '') {
                dropdown.style.display = 'block';
            } else {
                dropdown.style.display = 'none';
            }
        }

        function closeAddMenu() {
            document.getElementById('addMenuDropdown').style.display = 'none';
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function (e) {
            const dropdown = document.getElementById('addMenuDropdown');
            const btn = document.getElementById('addMenuBtn');
            if (dropdown && btn && !dropdown.contains(e.target) && !btn.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });

        function openChecklistModal() {
            const section = document.getElementById('checklistSection');
            if (!section) return;
            section.style.display = 'block';
            const input = document.getElementById('newChecklistItem');
            if (input) {
                input.scrollIntoView({ behavior: 'smooth', block: 'center' });
                setTimeout(() => input.focus(), 300);
            }
        }

        function openMembersModal() {
            const modal = document.getElementById('membersModal');
            if (modal) {
                modal.classList.add('active');
                setTimeout(() => document.getElementById('memberSearchInput')?.focus(), 100);
            }
        }

        function closeMembersModal() {
            const modal = document.getElementById('membersModal');
            if (modal) modal.classList.remove('active');
            const results = document.getElementById('memberSearchResults');
            if (results) { results.style.display = 'none'; results.innerHTML = ''; }
            const input = document.getElementById('memberSearchInput');
            if (input) input.value = '';
            const currentList = document.getElementById('currentMembersList');
            if (currentList) currentList.style.display = 'block';
        }

        // All workspace users for search
        const allWorkspaceUsers = @json($allActiveUsers->map(fn($u) => ['id' => $u->id, 'name' => $u->name]));
        let cardMemberIds = new Set(@json($card->members->pluck('id')));

        function searchMembers(query) {
            const results = document.getElementById('memberSearchResults');
            const currentList = document.getElementById('currentMembersList');

            if (!query.trim()) {
                results.style.display = 'none';
                results.innerHTML = '';
                if (currentList) currentList.style.display = 'block';
                return;
            }

            // Hide current members list when searching
            if (currentList) currentList.style.display = 'none';

            const q = query.toLowerCase();
            const matched = allWorkspaceUsers.filter(u => u.name.toLowerCase().includes(q));

            if (!matched.length) { results.style.display = 'none'; return; }

            results.innerHTML = matched.map(u => {
                const isMember = cardMemberIds.has(u.id);
                const initials = u.name.substring(0, 2).toUpperCase();
                const bg = isMember ? '#0052cc' : '#0052cc';
                return `<div onclick="toggleCardMember(${u.id},'${u.name.replace(/'/g, "\\'")}',${isMember},null)"
                    style="display:flex;align-items:center;gap:10px;padding:8px 12px;cursor:pointer;transition:background 0.15s;"
                    onmouseover="this.style.background='#2c333a'" onmouseout="this.style.background='transparent'">
                    <div style="width:32px;height:32px;border-radius:50%;background:${bg};color:#fff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:600;flex-shrink:0;">${initials}</div>
                    <span style="flex:1;font-size:14px;color:#b6c2cf;">${u.name}</span>
                    ${isMember ? '<span style="color:#579dff;font-size:16px;font-weight:bold;">✓</span>' : ''}
                </div>`;
            }).join('');
            results.style.display = 'block';
        }

        function toggleCardMember(userId, userName, isMember, rowEl) {
            if (isMember) {
                // Remove from card
                fetch(`/boards/${boardId}/lists/${listId}/cards/${cardId}/members/${userId}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
                }).then(r => r.json()).then(data => {
                    if (data.success) {
                        cardMemberIds.delete(userId);
                        // Remove from current list
                        const el = document.querySelector(`#currentMembersList [data-user-id="${userId}"]`);
                        if (el) el.remove();
                        // Update inline avatars
                        removeInlineMemberAvatar(userId);
                        // Refresh search
                        searchMembers(document.getElementById('memberSearchInput').value);
                        showToast(`${userName} removed`, 'success');
                    }
                });
            } else {
                // Add to card
                fetch(`/boards/${boardId}/lists/${listId}/cards/${cardId}/members`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                    body: JSON.stringify({ user_id: userId })
                }).then(r => r.json()).then(data => {
                    if (data.success) {
                        cardMemberIds.add(userId);
                        // Add to current list
                        const list = document.getElementById('currentMembersList');
                        const initials = userName.substring(0, 2).toUpperCase();
                        const div = document.createElement('div');
                        div.className = 'ms-item';
                        div.setAttribute('data-user-id', userId);
                        div.style.cssText = 'display:flex;align-items:center;gap:10px;padding:6px 8px;border-radius:4px;cursor:pointer;transition:background 0.15s;';
                        div.onmouseover = () => div.style.background = '#2c333a';
                        div.onmouseout = () => div.style.background = 'transparent';
                        div.onclick = () => toggleCardMember(userId, userName, true, div);
                        div.innerHTML = `<div style="width:32px;height:32px;border-radius:50%;background:#0052cc;color:#fff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:600;flex-shrink:0;">${initials}</div><span style="flex:1;font-size:14px;color:#b6c2cf;">${userName}</span><span style="color:#579dff;font-size:16px;font-weight:bold;">✓</span>`;
                        list.appendChild(div);
                        // Add inline avatar
                        addInlineMemberAvatar(userId, userName);
                        // Refresh search
                        searchMembers(document.getElementById('memberSearchInput').value);
                        showToast(`${userName} added`, 'success');
                    }
                });
            }
        }

        function addInlineMemberAvatar(userId, userName) {
            let container = document.querySelector('.card-members-inline');
            if (!container) return;
            if (container.querySelector(`[data-user-id="${userId}"]`)) return;
            const addBtn = container.querySelector('.add-member-btn');
            const wrapper = document.createElement('div');
            wrapper.setAttribute('data-user-id', userId);
            wrapper.style.cssText = 'position:relative;';
            wrapper.innerHTML = `<div class="member-avatar" title="${userName}" style="width:32px;height:32px;font-size:13px;cursor:pointer;background:#0052cc;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:600;">${userName.substring(0, 2).toUpperCase()}</div>`;
            if (addBtn) container.insertBefore(wrapper, addBtn);
            else container.appendChild(wrapper);
        }

        function removeInlineMemberAvatar(userId) {
            const el = document.querySelector(`.card-members-inline [data-user-id="${userId}"]`);
            if (el) el.remove();
        }

        function openDatesModal() {
            // Close all other modals first
            closeLabelsModal();
            closeCoverModal();
            closeMoveModal();
            closeMembersModal();
            document.getElementById('datesModal').classList.add('active');
        }

        function closeDatesModal() {
            document.getElementById('datesModal').classList.remove('active');
        }

        function updateDatesDisplay(startDate, dueDate) {
            // Find or create the dates metadata item
            let datesItem = document.getElementById('datesMetadataItem');
            const metaGrid = document.querySelector('.card-metadata-grid');
            if (!metaGrid) return;

            if (!startDate && !dueDate) {
                if (datesItem) datesItem.remove();
                return;
            }

            if (!datesItem) {
                datesItem = document.createElement('div');
                datesItem.id = 'datesMetadataItem';
                datesItem.className = 'metadata-item';
                datesItem.style.marginBottom = '20px';
                metaGrid.appendChild(datesItem);
            }

            const fmt = (d) => {
                if (!d) return '';
                const dt = new Date(d);
                return dt.toLocaleDateString('en-GB', { day: '2-digit', month: 'short' });
            };

            let dateText = '';
            if (startDate && dueDate) dateText = `${fmt(startDate)} - ${fmt(dueDate)}`;
            else if (startDate) dateText = fmt(startDate);
            else dateText = fmt(dueDate);

            datesItem.innerHTML = `
                <h3 style="font-size:12px;font-weight:600;color:#9fadbc;text-transform:uppercase;margin-bottom:8px;">Dates</h3>
                <div onclick="openDatesModal()" style="display:inline-flex;align-items:center;gap:8px;background:#3d444d;padding:6px 12px;border-radius:3px;cursor:pointer;color:#b6c2cf;font-size:14px;">
                    <span>${dateText}</span>
                </div>
            `;
        }

        async function saveDates() {
            const startDate = document.getElementById('startDateInput').value;
            const dueDate = document.getElementById('dueDateInput').value;

            try {
                const response = await fetch(`/boards/${boardId}/lists/${listId}/cards/${cardId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        start_date: startDate || null,
                        due_date: dueDate || null
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showToast('Dates saved successfully', 'success');
                    closeDatesModal();
                    updateDatesDisplay(startDate, dueDate);
                } else {
                    showToast('Failed to save dates', 'error');
                }
            } catch (error) {
                console.error('Error saving dates:', error);
                showToast('Failed to save dates', 'error');
            }
        }

        async function removeDates() {
            try {
                const response = await fetch(`/boards/${boardId}/lists/${listId}/cards/${cardId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        start_date: null,
                        due_date: null
                    })
                });
                const data = await response.json();
                if (data.success) {
                    showToast('Dates removed successfully', 'success');
                    closeDatesModal();
                    updateDatesDisplay(null, null);
                } else {
                    showToast('Failed to remove dates', 'error');
                }
            } catch (error) {
                console.error('Error removing dates:', error);
                showToast('Failed to remove dates', 'error');
            }
        }

        function openAttachmentModal() {
            const section = document.getElementById('attachmentsSection');
            if (section.style.display === 'none') {
                section.style.display = 'block';
            }
            // Trigger file input
            document.getElementById('attachmentFileInput').click();
        }

        function previewImage(src, name) {
            document.getElementById('imagePreviewImg').src = src;
            document.getElementById('imagePreviewName').textContent = name;
            document.getElementById('imagePreviewDownload').href = src;
            document.getElementById('imagePreviewDownload').download = name;
            document.getElementById('imagePreviewModal').classList.add('active');
        }

        function closeImagePreview() {
            document.getElementById('imagePreviewModal').classList.remove('active');
        }

        function toggleSidebar() {
            const sidebar = document.querySelector('.card-view-sidebar');
            sidebar.style.display = sidebar.style.display === 'none' ? 'block' : 'none';
        }

        function updateCardTitle() {
            const title = document.getElementById('cardTitleInput').value.trim();
            if (!title) {
                document.getElementById('cardTitleInput').value = '{{ $card->title }}';
                return;
            }

            fetch(`/boards/${boardId}/lists/${listId}/cards/${cardId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ title: title })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Title updated successfully
                    }
                })
                .catch(error => { });
        }

        function editDescription() {
            document.getElementById('descriptionDisplay').style.display = 'none';
            document.getElementById('descriptionEditor').style.display = 'block';
            if (window.descriptionQuill) { window.descriptionQuill.focus(); }
        }

        function cancelDescriptionEdit() {
            document.getElementById('descriptionEditor').style.display = 'none';
            document.getElementById('descriptionDisplay').style.display = 'block';
            if (window.descriptionQuill) {
                const originalHtml = document.getElementById('descriptionText').innerHTML;
                const activeHtml = originalHtml.includes('Add a more detailed description...') ? '' : originalHtml;
                window.descriptionQuill.root.innerHTML = activeHtml;
            }
        }

        function saveDescription() {
            if (!window.descriptionQuill) return;
            const plainText = window.descriptionQuill.getText().trim();
            const html = window.descriptionQuill.root.innerHTML;
            const hasImage = html.includes('<img');
            const description = (plainText || hasImage) ? html : '';

            fetch(`/boards/${boardId}/lists/${listId}/cards/${cardId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ description: description })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Description saved!', 'success');
                        // Update description display without reload
                        const descText = document.getElementById('descriptionText');
                        if (descText) {
                            descText.innerHTML = description || 'Add a more detailed description...';
                            descText.classList.toggle('empty', !description || !description.trim());
                        }
                        document.getElementById('descriptionEditor').style.display = 'none';
                        document.getElementById('descriptionDisplay').style.display = 'block';
                    }
                })
                .catch(error => showToast('Failed to save description', 'error'));
        }

        window.currentLabels = @json($cardLabels);
        window.boardLabels = @json($boardLabels->map(function ($l) {
            return ['id' => $l->id, 'name' => $l->name, 'color' => $l->color];
        }));

        // Extended Trello color palette (matches the grid in the screenshot)
        const trelloColors = [
            '#b8f5d0', '#f5ea92', '#f5d6a1', '#f5b1a1', '#e1bdf5',
            '#4bce97', '#e2b203', '#faa53d', '#f87168', '#9f8fef',
            '#1f845a', '#946f00', '#b65d13', '#ca3521', '#6e5dc6',
            '#cce0ff', '#b3f5ff', '#d3f1a7', '#fdd0ec', '#dcdfe4',
            '#579dff', '#60c6d2', '#94c74a', '#e774bb', '#8590a2',
            '#0c66e4', '#1d7f8c', '#5b7f24', '#ae4787', '#626f81'
        ];

        let currentEditingLabelId = null;
        let selectedLabelColor = '#4bce97';

        function filterLabels() {
            const searchTerm = document.getElementById('labelSearchInput').value.toLowerCase();
            const items = document.querySelectorAll('#labelOptions .label-list-item');

            items.forEach(item => {
                const name = item.getAttribute('data-name').toLowerCase();
                if (name.includes(searchTerm)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        // Open main labels modal
        function openLabelsModal() {
            document.getElementById('labelsModal').classList.add('active');
        }

        // Close main labels modal
        function closeLabelsModal() {
            document.getElementById('labelsModal').classList.remove('active');
        }


        function saveLabels() {
            // Get all checked checkboxes
            const checkedBoxes = document.querySelectorAll('#labelOptions .label-list-item input[type="checkbox"]:checked');
            const payload = Array.from(checkedBoxes).map(cb => {
                const row = cb.closest('.label-list-item');
                const id = row.getAttribute('data-id');
                return parseInt(id);
            });

            fetch(`/boards/${boardId}/lists/${listId}/cards/${cardId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ labels: payload })
            })
                .then(response => response.json())
                .then(data => {
                    const card = data.card || data;
                    if (card && card.labels) {
                        window.currentLabels = [...card.labels];
                        updateLabelsDisplay(window.currentLabels);
                        window.selectedLabels = [...card.labels];
                        updateBoardCardLabels(cardId, card.labels);
                    }
                })
                .catch(error => console.error('Error:', error));

        }

        function updateLabelsDisplay(labels) {
            const container = document.getElementById('cardLabelsList');
            const item = document.getElementById('labelsMetadataItem');

            if (!labels || labels.length === 0) {
                if (item) item.style.display = 'none';
                if (container) container.innerHTML = '<div onclick="openLabelsModal()" style="width:32px; height:32px; border-radius:4px; background:#3d444d; color:#9fadbc; display:flex; align-items:center; justify-content:center; font-size:18px; cursor:pointer;" title="Add labels">+</div>';
                return;
            }

            if (item) item.style.display = 'block';
            if (!container) return;

            container.innerHTML = '';

            labels.forEach(labelRef => {
                let color = '';
                let name = '';

                // Always use window.boardLabels so updated data is used
                if (typeof labelRef === 'number' || (typeof labelRef === 'string' && !isNaN(labelRef))) {
                    const fullLabel = window.boardLabels.find(l => l.id == labelRef);
                    if (fullLabel) { color = fullLabel.color; name = fullLabel.name; }
                } else if (labelRef && typeof labelRef === 'object') {
                    color = labelRef.color || '';
                    name = labelRef.name || '';
                } else if (typeof labelRef === 'string') {
                    const labelColors = { 'green': '#61bd4f', 'yellow': '#f2d600', 'orange': '#ff9f1a', 'red': '#eb5a46', 'purple': '#c377e0', 'blue': '#0079bf' };
                    color = labelColors[labelRef] || '#61bd4f';
                    name = labelRef.charAt(0).toUpperCase() + labelRef.slice(1);
                }

                if (color) {
                    const labelDiv = document.createElement('div');
                    labelDiv.onclick = openLabelsModal;
                    labelDiv.style.cssText = `background: ${color}; color: #fff; min-width: 48px; height: 32px; padding: 0 12px; border-radius: 4px; font-size: 14px; font-weight: 600; display: flex; align-items: center; justify-content: center; cursor: pointer;`;
                    labelDiv.textContent = name;
                    container.appendChild(labelDiv);
                }
            });

            const addBtn = document.createElement('div');
            addBtn.onclick = openLabelsModal;
            addBtn.style.cssText = 'width:32px; height:32px; border-radius:4px; background:#3d444d; color:#9fadbc; display:flex; align-items:center; justify-content:center; font-size:18px; cursor:pointer;';
            addBtn.textContent = '+';
            addBtn.title = "Add labels";
            container.appendChild(addBtn);
        }


        // Open edit label modal
        function openEditLabelModal(labelId, labelName, labelColor) {
            currentEditingLabelId = labelId;
            selectedLabelColor = labelColor;

            document.getElementById('editLabelTitle').textContent = 'Edit label';
            document.getElementById('editLabelNameInput').value = labelName;
            document.getElementById('deleteLabelBtn').style.display = 'block';

            updateLabelPreview();
            populateColorGrid();

            document.getElementById('editLabelModal').classList.add('active');
        }

        // Open create label modal
        function openCreateLabelModal() {
            currentEditingLabelId = null;
            selectedLabelColor = '#61bd4f';

            document.getElementById('editLabelTitle').textContent = 'Create label';
            document.getElementById('editLabelNameInput').value = '';
            document.getElementById('deleteLabelBtn').style.display = 'none';

            updateLabelPreview();
            populateColorGrid();

            document.getElementById('editLabelModal').classList.add('active');
        }

        // Close edit/create label modal
        function closeEditLabelModal() {
            document.getElementById('editLabelModal').classList.remove('active');
            currentEditingLabelId = null;
        }

        function removeLabelColor() {
            selectedLabelColor = '#b3bac5'; // Gray/Transparentish color
            document.querySelectorAll('.color-box').forEach(box => box.classList.remove('selected'));
            updateLabelPreview();
        }

        // Populate color grid
        function populateColorGrid() {
            const colorGrid = document.getElementById('colorGrid');
            colorGrid.innerHTML = '';

            trelloColors.forEach(color => {
                const colorBox = document.createElement('div');
                colorBox.className = 'color-box';
                if (color === selectedLabelColor) {
                    colorBox.classList.add('selected');
                }
                colorBox.style.background = color;
                colorBox.onclick = function () {
                    selectedLabelColor = color;
                    document.querySelectorAll('.color-box').forEach(box => box.classList.remove('selected'));
                    colorBox.classList.add('selected');
                    updateLabelPreview();
                };
                colorGrid.appendChild(colorBox);
            });
        }

        // Update label preview
        function updateLabelPreview() {
            const preview = document.getElementById('labelPreview');
            const nameInput = document.getElementById('editLabelNameInput');
            const previewText = document.getElementById('labelPreviewText');

            if (preview && nameInput && previewText) {
                preview.style.background = selectedLabelColor;
                previewText.textContent = nameInput.value || '';

                // Color contrast for yellow-like colors
                const isLightColor = ['#f5ea92', '#f2d600', '#f5ea92'].includes(selectedLabelColor.toLowerCase());
                preview.style.color = isLightColor ? '#172b4d' : '#ffffff';
            }
        }

        // Save label changes (create or update)
        function saveLabelChanges() {
            const labelName = document.getElementById('editLabelNameInput').value.trim();
            if (!labelName) {
                return;
            }

            if (currentEditingLabelId) {
                // Update existing label
                fetch(`/boards/${boardId}/labels/${currentEditingLabelId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ name: labelName, color: selectedLabelColor })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update in boardLabels
                            const labelIndex = window.boardLabels.findIndex(l => l.id === currentEditingLabelId);
                            if (labelIndex !== -1) {
                                window.boardLabels[labelIndex] = data.label;
                            }

                            // Update in DOM
                            const labelItem = document.querySelector(`.label-list-item[data-id="${currentEditingLabelId}"]`);
                            if (labelItem) {
                                const isLight = ['#f5ea92', '#f2d600'].includes(data.label.color.toLowerCase());
                                const textColor = isLight ? '#172b4d' : '#ffffff';
                                labelItem.setAttribute('data-name', data.label.name);
                                labelItem.setAttribute('data-color', data.label.color);
                                const bar = labelItem.querySelector('.label-full-bar');
                                bar.style.background = data.label.color;
                                bar.style.color = textColor;
                                const span = bar.querySelector('span');
                                if (span) span.textContent = data.label.name;
                                else bar.textContent = data.label.name;

                                // Update onclick
                                bar.onclick = function () { toggleLabelBar(this); };
                                labelItem.querySelector('.label-list-edit').onclick = function (e) {
                                    e.stopPropagation();
                                    openEditLabelModal(data.label.id, data.label.name, data.label.color);
                                };
                            }

                            // Update currentLabels if assigned
                            const currentLabelIndex = window.currentLabels.findIndex(l => l.id === currentEditingLabelId);
                            if (currentLabelIndex !== -1) {
                                window.currentLabels[currentLabelIndex] = data.label;
                            }
                            // Always refresh display so color/name changes show immediately
                            updateLabelsDisplay(window.currentLabels);

                            closeEditLabelModal();
                        }
                    })
                    .catch(error => { });
            } else {
                // Create new label
                fetch(`/boards/${boardId}/labels`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ name: labelName, color: selectedLabelColor })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.label) {
                            // Add to boardLabels
                            window.boardLabels.push(data.label);

                            // Add to DOM
                            const container = document.getElementById('labelOptions');
                            const textColor = labelName.toLowerCase() === 'yellow' ? '#172b4d' : '#ffffff';

                            const newLabelHtml = `
                            <div class="label-list-item" data-id="${data.label.id}" data-color="${data.label.color}" data-name="${data.label.name}" 
                                style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                                <input type="checkbox" checked onchange="toggleLabelCheckbox(this)" 
                                    style="width: 16px; height: 16px; cursor: pointer; accent-color: #579dff;">
                                <div class="label-full-bar" onclick="toggleLabelBar(this)" 
                                    style="flex: 1; height: 32px; background:${data.label.color}; color: ${textColor}; border-radius: 4px; padding: 0 12px; display: flex; align-items: center; font-size: 14px; font-weight: 600; cursor: pointer; min-width: 0;">
                                    <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${data.label.name}</span>
                                </div>
                                <button class="label-list-edit" title="Edit label" onclick="event.stopPropagation(); openEditLabelModal(${data.label.id}, '${data.label.name.replace(/'/g, "\\'")}', '${data.label.color}')" 
                                    style="background: none; border: none; color: #9fadbc; cursor: pointer; font-size: 16px; padding: 4px; border-radius: 3px; display: flex; align-items: center; justify-content: center; transition: background 0.2s;">
                                    ✏️
                                </button>
                            </div>
                        `;

                            container.insertAdjacentHTML('beforeend', newLabelHtml);

                            // Auto-assign to card
                            const checkbox = container.querySelector(`.label-list-item[data-id="${data.label.id}"] input[type=checkbox]`);
                            if (checkbox) {
                                toggleLabelCheckbox(checkbox);
                            }

                            closeEditLabelModal();
                        }
                    })
                    .catch(error => { });
            }
        }

        function deleteLabel() {
            if (!currentEditingLabelId) return;

            Swal.fire({
                title: 'Delete label?',
                text: "Are you sure you want to delete this label? It will be removed from all cards.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#eb5a46',
                cancelButtonColor: '#38414a',
                confirmButtonText: 'Yes, delete it',
                cancelButtonText: 'Cancel',
                background: '#22272b',
                color: '#b6c2cf',
                customClass: {
                    popup: 'swal2-dark',
                    confirmButton: 'swal2-confirm-dark',
                    cancelButton: 'swal2-cancel-dark'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/boards/${boardId}/labels/${currentEditingLabelId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Remove from boardLabels
                                window.boardLabels = window.boardLabels.filter(l => l.id != currentEditingLabelId);
                                // Remove from DOM
                                document.querySelectorAll(`.label-list-item[data-id="${currentEditingLabelId}"]`).forEach(el => el.remove());
                                // Remove from currentLabels if assigned
                                window.currentLabels = window.currentLabels.filter(id => id != currentEditingLabelId);

                                goBackToLabels();
                                showToast('Label deleted', 'success');
                            }
                        });
                }
            });
        }

        // Labels display mapped to grid now. Removing duplicate



        function getLabelColor(labelName) {
            const colors = {
                'green': '#61bd4f',
                'yellow': '#f2d600',
                'orange': '#ff9f1a',
                'red': '#eb5a46',
                'purple': '#c377e0',
                'blue': '#0079bf'
            };
            return colors[labelName.toLowerCase()] || '#b3b3b3';
        }

        let originalCover = null;

        function openCoverModal() {
            originalCover = document.querySelector('.card-cover-display')?.style.cssText || null;
            document.getElementById('coverModal').classList.add('active');
            loadCoverAttachments();
        }

        function loadCoverAttachments() {
            const attachmentsSection = document.getElementById('coverAttachmentsSection');
            const attachmentsGrid = document.getElementById('coverAttachmentsGrid');

            // Get all image attachments from the page
            const imageAttachments = [];
            document.querySelectorAll('.attachment-item img').forEach(img => {
                const attachmentItem = img.closest('.attachment-item');
                if (attachmentItem) {
                    imageAttachments.push({
                        id: attachmentItem.dataset.attachmentId,
                        url: img.src,
                        name: img.alt
                    });
                }
            });

            if (imageAttachments.length > 0) {
                attachmentsSection.style.display = 'block';
                attachmentsGrid.innerHTML = '';

                imageAttachments.forEach(attachment => {
                    const imgOption = document.createElement('div');
                    imgOption.className = 'cover-color-option';
                    imgOption.style.cssText = `background-image: url('${attachment.url}'); background-size: cover; background-position: center;`;
                    imgOption.onclick = () => selectCoverImage(attachment.url, imgOption);
                    attachmentsGrid.appendChild(imgOption);
                });
            } else {
                attachmentsSection.style.display = 'none';
            }
        }

        function selectCoverImage(imageUrl, element) {
            selectedCoverColor = imageUrl;
            document.querySelectorAll('.cover-color-option').forEach(el => el.classList.remove('selected'));
            element.classList.add('selected');
        }

        function makeAttachmentCover(imageUrl) {
            fetch(`/boards/${boardId}/lists/${listId}/cards/${cardId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ cover: imageUrl })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateCoverDisplay(imageUrl);
                        updateBoardCardCover(cardId, imageUrl);
                        showToast('Cover updated!', 'success');
                    }
                })
                .catch(error => showToast('Failed to update cover', 'error'));
        }

        function closeCoverModal() {
            document.getElementById('coverModal').classList.remove('active');
            selectedCoverColor = null;
            document.querySelectorAll('.cover-color-option, .unsplash-image').forEach(el => el.classList.remove('selected'));
        }

        function selectCoverColor(color, element) {
            selectedCoverColor = color;
            document.querySelectorAll('.cover-color-option, .unsplash-image').forEach(el => el.classList.remove('selected'));
            if (element) element.classList.add('selected');
            // Live preview
            updateCoverDisplay(color);
        }

        function removeCover() {
            fetch(`/boards/${boardId}/lists/${listId}/cards/${cardId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ cover: '' })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateCoverDisplay('');
                        closeCoverModal();
                        showToast('Cover removed successfully', 'success');
                    }
                })
                .catch(error => { });
        }

        function updateCoverDisplay(coverUrl) {
            let coverDisplay = document.querySelector('.card-cover-display');
            const isColor = coverUrl && /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(coverUrl);

            // No cover - hide
            if (!coverUrl) {
                if (coverDisplay) {
                    coverDisplay.className = 'card-cover-display no-cover';
                    coverDisplay.style.cssText = 'height: 8px;';
                    coverDisplay.innerHTML = '';
                }
                return;
            }

            // Create if missing
            if (!coverDisplay) {
                coverDisplay = document.createElement('div');
                const container = document.querySelector('.card-view-container');
                if (container) container.insertBefore(coverDisplay, container.firstChild);
            }

            if (isColor) {
                // Solid color cover
                coverDisplay.className = 'card-cover-display';
                coverDisplay.style.cssText = `background-color: ${coverUrl}; height: 176px;`;
                coverDisplay.innerHTML = '';
            } else {
                // Image cover - blurred bg + centered image (Trello style)
                coverDisplay.className = 'card-cover-display cover-image';
                coverDisplay.style.cssText = `--cover-url: url('${coverUrl}'); background-image: url('${coverUrl}'); height: 176px;`;
                // Replace img inside
                let img = coverDisplay.querySelector('img');
                if (!img) {
                    img = document.createElement('img');
                    img.alt = 'cover';
                    img.onerror = function () { this.closest('.card-cover-display').style.display = 'none'; };
                    coverDisplay.appendChild(img);
                }
                img.src = coverUrl;
            }
        }

        function saveCover() {
            if (!selectedCoverColor) {
                closeCoverModal();
                return;
            }

            fetch(`/boards/${boardId}/lists/${listId}/cards/${cardId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ cover: selectedCoverColor })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateCoverDisplay(selectedCoverColor);
                        updateBoardCardCover(cardId, selectedCoverColor);
                        closeCoverModal();
                        showToast('Cover updated', 'success');
                    }
                })
                .catch(error => { });
        }

        function uploadCoverImage(event) {
            const file = event.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('file', file);

            fetch(`/boards/${boardId}/lists/${listId}/cards/${cardId}/upload-cover`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateCoverDisplay(data.cover);
                        updateBoardCardCover(cardId, data.cover);
                        showToast('Cover updated!', 'success');
                    }
                })
                .catch(error => showToast('Failed to upload cover', 'error'));
        }

        function openMoveModal() {
            document.getElementById('moveModal').classList.add('active');
            document.getElementById('moreMenu').style.display = 'none';
        }

        function closeMoveModal() {
            document.getElementById('moveModal').classList.remove('active');
        }

        function saveMove() {
            const targetListId = document.getElementById('moveListSelect').value;

            // Validate that we have a list ID
            if (!targetListId) {
                Swal.fire({
                    icon: 'error',
                    title: 'Please select a list',
                    background: '#22272b',
                    color: '#b6c2cf'
                });
                return;
            }

            fetch(`/boards/${boardId}/lists/${listId}/cards/${cardId}/move`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    list_id: parseInt(targetListId),
                    position: 0
                })
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        window.location.href = '{{ route('boards.show', $board) }}';
                    }
                })
                .catch(error => {
                    console.error('Move error:', error);
                });
        }

        function archiveCard() {
            console.log('Archive card clicked');
            Swal.fire({
                title: 'Archive card?',
                text: "Are you sure you want to archive this card? You can restore it later.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#0052cc',
                cancelButtonColor: '#38414a',
                confirmButtonText: 'Yes, archive it',
                cancelButtonText: 'Cancel',
                background: '#22272b',
                color: '#b6c2cf',
                customClass: {
                    popup: 'swal2-dark',
                    confirmButton: 'swal2-confirm-dark',
                    cancelButton: 'swal2-cancel-dark'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    console.log('Archiving card:', cardId);
                    fetch(`/boards/${boardId}/lists/${listId}/cards/${cardId}/archive`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            console.log('Archive response:', data);
                            if (data.success) {
                                console.log('Card archived successfully, redirecting to board');
                                showToast('Card archived successfully', 'success');
                                setTimeout(() => {
                                    window.location.href = '{{ route('boards.show', $board) }}';
                                }, 500);
                            } else {
                                console.error('Archive failed:', data);
                                showToast('Failed to archive card', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Archive error:', error);
                            showToast('Failed to archive card', 'error');
                        });
                }
            });
        }

        function deleteCard() {
            Swal.fire({
                title: 'Delete card?',
                text: "Are you sure you want to delete this card? This action cannot be undone.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#eb5a46',
                cancelButtonColor: '#38414a',
                confirmButtonText: 'Yes, delete it',
                cancelButtonText: 'Cancel',
                background: '#22272b',
                color: '#b6c2cf',
                customClass: {
                    popup: 'swal2-dark',
                    confirmButton: 'swal2-confirm-dark',
                    cancelButton: 'swal2-cancel-dark'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/boards/${boardId}/lists/${listId}/cards/${cardId}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    })
                        .then(response => {
                            if (response.ok) {
                                window.location.href = '{{ route('boards.show', $board) }}';
                            }
                        })
                        .catch(error => {
                            showToast('Failed to delete card', 'error');
                        });
                }
            });
        }

        // Close dropdowns on outside click
        document.addEventListener('click', function (e) {
            if (!e.target.closest('.list-dropdown') && !e.target.closest('#listDropdown')) {
                document.getElementById('listDropdown').style.display = 'none';
            }
            if (!e.target.closest('.header-icon-btn[onclick*="openMoreMenu"]') && !e.target.closest('#moreMenu')) {
                document.getElementById('moreMenu').style.display = 'none';
            }
        });

        // Close on Escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                const modals = document.querySelectorAll('.modal-overlay.active');
                if (modals.length > 0) {
                    modals.forEach(modal => modal.classList.remove('active'));
                } else {
                    document.querySelectorAll('.dropdown-menu').forEach(menu => menu.style.display = 'none');
                }
            }
        });

        // Enter key to submit comment
        const commentInput = document.getElementById('commentInput');
        if (commentInput) {
            commentInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' && e.ctrlKey) {
                    addComment();
                }
            });
        }

        function updateChecklistProgress() {
            const items = document.querySelectorAll('.checklist-item input[type="checkbox"]');
            const total = items.length;
            const completed = Array.from(items).filter(i => i.checked).length;
            const percent = total > 0 ? Math.round((completed / total) * 100) : 0;

            const progressBar = document.getElementById('checklistProgressBar');
            const percentText = document.getElementById('checklistPercent');
            const section = document.getElementById('checklistSection');

            if (progressBar) progressBar.style.width = percent + '%';
            if (percentText) percentText.textContent = percent + '%';
            if (section) section.style.display = total > 0 ? 'block' : 'none';
        }

        // Checklist Functions
        function addChecklistItem() {
            const input = document.getElementById('newChecklistItem');
            const title = input.value.trim();
            if (!title) return;

            fetch(`/boards/${boardId}/lists/${listId}/cards/${cardId}/checklist-items`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ title: title })
            })
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(t => { throw new Error(t); });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        const item = data.item;
                        const checklistDiv = document.getElementById('checklistItems');
                        const section = document.getElementById('checklistSection');
                        if (section) section.style.display = 'block';
                        const itemDiv = document.createElement('div');
                        itemDiv.className = 'checklist-item';
                        itemDiv.setAttribute('data-item-id', item.id);
                        itemDiv.style.cssText = 'display: flex; align-items: flex-start; gap: 12px; margin-bottom: 8px; padding: 4px; border-radius: 4px;';
                        itemDiv.innerHTML = `
                        <input type="checkbox" id="checklist-${item.id}" onchange="toggleChecklistItem(${item.id}, this.checked)" style="width: 16px; height: 16px; margin-top: 2px; cursor: pointer;">
                        <label for="checklist-${item.id}" class="checklist-label" style="flex: 1; font-size: 14px; color: #b6c2cf; cursor: pointer;">${item.title}</label>
                        <button class="checklist-delete" onclick="deleteChecklistItem(${item.id})" title="Delete" style="background: transparent; border: none; color: #9fadbc; cursor: pointer; padding: 0 4px; font-size: 18px;">×</button>
                    `;
                        checklistDiv.appendChild(itemDiv);
                        input.value = '';
                        updateChecklistProgress();
                    }
                })
                .catch(error => { alert('Checklist error: ' + error.message); });
        }

        function toggleChecklistItem(itemId, isCompleted) {
            fetch(`/boards/${boardId}/lists/${listId}/cards/${cardId}/checklist-items/${itemId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ is_completed: isCompleted })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const item = document.querySelector(`[data-item-id="${itemId}"]`);
                        const label = item.querySelector('label');
                        if (isCompleted) {
                            label.classList.add('completed');
                        } else {
                            label.classList.remove('completed');
                        }
                        updateChecklistProgress();
                    }
                })
                .catch(error => { });
        }

        function deleteChecklistItem(itemId) {
            fetch(`/boards/${boardId}/lists/${listId}/cards/${cardId}/checklist-items/${itemId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.querySelector(`[data-item-id="${itemId}"]`).remove();
                        updateChecklistProgress();
                    }
                })
                .catch(error => { });
        }

        // Members Functions
        function filterMembers() { /* replaced by searchMembers */ }

        function toggleMember(userId, element) { /* replaced by toggleCardMember */ }

        function addMember(userId) { toggleCardMember(userId, '', false, null); }
        function removeMember(userId) { toggleCardMember(userId, '', true, null); }

        // Attachment Functions
        function uploadAttachment(event) {
            const files = Array.from(event.target.files);
            if (!files.length) return;

            files.forEach(file => uploadSingleAttachment(file));
            event.target.value = '';
        }

        function uploadSingleAttachment(file) {
            const formData = new FormData();
            formData.append('file', file);

            fetch(`/boards/${boardId}/lists/${listId}/cards/${cardId}/attachments`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const att = data.attachment;
                        const fileUrl = `/storage/${att.file_path}`;
                        const isImage = /\.(jpg|jpeg|png|gif|webp|svg)$/i.test(att.name);
                        const ext = att.name.split('.').pop().toUpperCase();

                        // Show attachments section if hidden
                        let section = document.querySelector('.attachments-section');
                        if (!section) {
                            // Create section dynamically
                            const main = document.querySelector('.card-view-main');
                            section = document.createElement('div');
                            section.className = 'attachments-section';
                            section.style.marginBottom = '32px';
                            section.innerHTML = `
                            <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
                                <svg viewBox="0 0 24 24" fill="currentColor" style="width:20px;height:20px;color:#9fadbc;"><path d="M16.5 6v11.5c0 2.21-1.79 4-4 4s-4-1.79-4-4V5c0-1.38 1.12-2.5 2.5-2.5s2.5 1.12 2.5 2.5v10.5c0 .55-.45 1-1 1s-1-.45-1-1V6H10v9.5c0 1.38 1.12 2.5 2.5 2.5s2.5-1.12 2.5-2.5V5c0-2.21-1.79-4-4-4S7 2.79 7 5v12.5c0 3.04 2.46 5.5 5.5 5.5s5.5-2.46 5.5-5.5V6h-1.5z"/></svg>
                                <h3 style="font-size:16px;font-weight:600;color:#b6c2cf;margin:0;">Attachments</h3>
                                <button class="btn-cancel" onclick="document.getElementById('attachmentFileInput').click()" style="margin-left:auto;">Add</button>
                            </div>
                            <div style="padding-left:36px;" id="attachmentsList"></div>
                        `;
                            const checklistSection = document.getElementById('checklistSection');
                            main.insertBefore(section, checklistSection ? checklistSection.nextSibling : null);
                        }

                        const list = document.getElementById('attachmentsList');
                        const itemDiv = document.createElement('div');
                        itemDiv.className = 'attachment-item';
                        itemDiv.setAttribute('data-attachment-id', att.id);
                        itemDiv.style.cssText = 'display:flex;gap:16px;margin-bottom:12px;padding:8px;border-radius:6px;cursor:pointer;';
                        itemDiv.onmouseover = () => itemDiv.style.background = '#2c333a';
                        itemDiv.onmouseout = () => itemDiv.style.background = 'transparent';

                        itemDiv.innerHTML = isImage
                            ? `<div onclick="previewImage('${fileUrl}','${att.name}')" style="width:112px;height:80px;background:#3d444d;border-radius:3px;background-image:url('${fileUrl}');background-size:cover;background-position:center;flex-shrink:0;"></div>
                           <div style="flex:1;min-width:0;display:flex;flex-direction:column;justify-content:center;">
                               <div style="font-size:14px;font-weight:700;color:#b6c2cf;">${att.name}</div>
                               <div style="font-size:12px;color:#9fadbc;margin:4px 0 8px;">Just now</div>
                               <div style="display:flex;gap:12px;font-size:13px;">
                                   <span style="color:#9fadbc;text-decoration:underline;cursor:pointer;" onclick="deleteAttachment(${att.id})">Delete</span>
                                   <span style="color:#9fadbc;text-decoration:underline;cursor:pointer;" onclick="makeAttachmentCover('${fileUrl}')">Make cover</span>
                               </div>
                           </div>`
                            : `<div style="width:112px;height:80px;background:#3d444d;border-radius:3px;display:flex;align-items:center;justify-content:center;font-size:18px;color:#9fadbc;font-weight:bold;flex-shrink:0;">${ext}</div>
                           <div style="flex:1;min-width:0;display:flex;flex-direction:column;justify-content:center;">
                               <div style="font-size:14px;font-weight:700;color:#b6c2cf;">${att.name}</div>
                               <div style="font-size:12px;color:#9fadbc;margin:4px 0 8px;">Just now</div>
                               <div style="display:flex;gap:12px;font-size:13px;">
                                   <a href="${fileUrl}" download="${att.name}" style="color:#9fadbc;text-decoration:underline;">Download</a>
                                   <span style="color:#9fadbc;text-decoration:underline;cursor:pointer;" onclick="deleteAttachment(${att.id})">Delete</span>
                               </div>
                           </div>`;

                        list.prepend(itemDiv);
                        showToast('Attachment uploaded', 'success');
                    }
                })
                .catch(error => {
                    showToast('Failed to upload attachment', 'error');
                });
        }

        function deleteAttachment(attachmentId) {
            Swal.fire({
                title: 'Delete attachment?',
                text: "Are you sure you want to delete this attachment?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#eb5a46',
                cancelButtonColor: '#38414a',
                confirmButtonText: 'Yes, delete it',
                cancelButtonText: 'Cancel',
                background: '#22272b',
                color: '#b6c2cf',
                customClass: {
                    popup: 'swal2-dark',
                    confirmButton: 'swal2-confirm-dark',
                    cancelButton: 'swal2-cancel-dark'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/boards/${boardId}/lists/${listId}/cards/${cardId}/attachments/${attachmentId}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.querySelector(`[data-attachment-id="${attachmentId}"]`).remove();
                                showToast('Attachment deleted', 'success');
                            }
                        })
                        .catch(error => {
                            showToast('Failed to delete attachment', 'error');
                        });
                }
            });
        }

        function toggleMembersDropdown() {
            openMembersModal();
        }
    </script>
    <style>
        /* Modern Quill Styling to match Trello */
        .ql-toolbar.ql-snow {
            border: none !important;
            background: transparent !important;
            padding: 4px 8px !important;
            border-bottom: 1px solid #3c444d !important;
            display: flex !important;
            align-items: center;
            flex-wrap: wrap;
            /* Always show toolbar */
            visibility: visible !important;
        }

        .ql-toolbar.ql-snow .ql-formats {
            display: flex !important;
            align-items: center;
            margin-right: 8px !important;
        }

        .ql-container.ql-snow {
            border: none !important;
            font-family: 'Inter', -apple-system, sans-serif !important;
            font-size: 14px !important;
            color: #b6c2cf !important;
        }

        .ql-editor {
            padding: 12px 16px !important;
            min-height: 40px;
            transition: min-height 0.2s ease;
        }

        .ql-editor.ql-blank::before {
            color: #8c9bab !important;
            font-style: normal !important;
            left: 16px !important;
        }

        .comment-editor-wrapper,
        .description-editor-wrapper {
            background: #22272b;
            border: 1px solid #3c444d;
            border-radius: 8px;
            overflow: visible !important; /* Allow menus to pop out */
            transition: border-color 0.2s, box-shadow 0.2s;
            margin-bottom: 12px;
            position: relative;
        }

        .comment-editor-wrapper.focused,
        .description-editor-wrapper.focused {
            border-color: #579dff;
            box-shadow: 0 0 0 1px #579dff;
            background: #1d2125;
        }

        .ql-snow .ql-stroke {
            stroke: #9fadbc !important;
        }

        .ql-snow .ql-fill {
            fill: #9fadbc !important;
        }

        .ql-snow .ql-picker {
            color: #9fadbc !important;
        }

        .ql-snow.ql-toolbar button:hover .ql-stroke,
        .ql-snow.ql-toolbar button.ql-active .ql-stroke {
            stroke: #579dff !important;
        }

        .ql-snow.ql-toolbar button:hover .ql-fill,
        .ql-snow.ql-toolbar button.ql-active .ql-fill {
            fill: #b6c2cf !important;
        }

        /* Rendered Comment Fixes */
        .comment-body {
            font-size: 14px !important;
            line-height: 1.5 !important;
            color: #b6c2cf !important;
        }

        .comment-body p,
        .comment-body ol,
        .comment-body ul {
            margin: 0 0 8px 0 !important;
            padding: 0 !important;
        }

        .comment-body ol,
        .comment-body ul {
            padding-left: 20px !important;
        }

        .comment-body li {
            font-size: 14px !important;
            margin-bottom: 4px !important;
        }

        .comment-body img {
            max-width: 100% !important;
            border-radius: 4px !important;
            margin-top: 8px !important;
            display: block !important;
            border: 1px solid #3c444d !important;
        }

        /* If image is inside a list item */
        .comment-body li img {
            margin-left: -20px !important;
            /* Move out of list indentation if needed */
            margin-top: 10px !important;
            width: calc(100% + 20px) !important;
        }

        .ql-picker-options {
            background-color: #22272b !important;
            border: 1px solid #3c444d !important;
            color: #b6c2cf !important;
        }

        /* Premium Tooltips */
        .ql-toolbar button, 
        .ql-toolbar .ql-picker {
            position: relative;
        }

        .ql-toolbar button::after,
        .ql-toolbar .ql-picker::after {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%) translateY(-5px);
            padding: 4px 8px;
            background: #101204;
            color: #fff;
            font-size: 11px;
            border-radius: 4px;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s, transform 0.2s;
            z-index: 1000;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
            border: 1px solid #3c444d;
        }

        .ql-toolbar button:hover::after,
        .ql-toolbar .ql-picker:hover::after {
            opacity: 1;
            transform: translateX(-50%) translateY(-8px);
        }

        /* Specific style for Heading picker to look like Trello */
        .ql-snow .ql-picker.ql-header {
            width: 36px !important;
            height: 32px !important;
            margin-right: 4px !important;
            display: flex !important;
            align-items: center;
            justify-content: center;
        }
        
        .ql-snow .ql-picker.ql-header .ql-picker-label {
            padding: 0 !important;
            display: flex !important;
            align-items: center;
            justify-content: center;
            border: none !important;
        }

        .ql-snow .ql-picker.ql-header .ql-picker-label svg {
            display: none !important; /* Hide giant default arrows */
        }
        
        .ql-snow .ql-picker.ql-header .ql-picker-label::before {
            content: 'Tt' !important;
            font-weight: 600;
            color: #9fadbc;
            font-size: 14px;
            line-height: 1;
            transition: color 0.2s;
        }

        .ql-snow .ql-picker.ql-header.ql-active .ql-picker-label::before,
        .ql-snow .ql-picker.ql-header:hover .ql-picker-label::before {
            color: #579dff !important;
        }

        /* Premium Picker Options - Heavy Overrides */
        .ql-snow.ql-toolbar .ql-picker-options {
            background-color: #22272b !important;
            border: 1px solid #454f59 !important;
            padding: 8px 0 !important;
            border-radius: 6px !important;
            box-shadow: 0 12px 24px rgba(0,0,0,0.6) !important;
            min-width: 220px !important;
            width: 220px !important;
            z-index: 9999 !important;
            overflow: visible !important;
            margin-top: 8px !important;
            left: 0 !important; /* Align to button start */
            transform: none !important;
        }

        .ql-snow.ql-toolbar .ql-picker-item {
            padding: 6px 16px !important; /* More compact */
            height: auto !important;
            line-height: 1.5 !important;
            color: #b6c2cf !important;
            font-size: 14px !important;
            font-family: 'Inter', sans-serif !important;
            display: flex !important;
            align-items: center;
            justify-content: space-between;
            border: none !important;
            background: transparent !important;
            width: 100% !important;
            margin: 2px 0;
        }

        /* Separator after Normal Text */
        .ql-snow.ql-toolbar .ql-picker.ql-header .ql-picker-item:not([data-value]) {
            border-bottom: 1px solid #3c444d !important;
            margin-bottom: 6px !important;
            padding-bottom: 10px !important;
        }

        /* Trello-style Hierarchy & Shortcuts */
        .ql-snow.ql-toolbar .ql-picker.ql-header .ql-picker-item::before {
            font-family: 'Inter', sans-serif !important;
        }

        /* Normal Text - Top */
        .ql-snow.ql-toolbar .ql-picker.ql-header .ql-picker-item:not([data-value])::before { content: 'Normal Text' !important; font-size: 14px !important; color: #b6c2cf !important; }
        .ql-snow.ql-toolbar .ql-picker.ql-header .ql-picker-item:not([data-value])::after { content: 'Ctrl+Alt+0'; font-size: 10px; color: #8c9bab; opacity: 0.8; }

        /* Heading 1-6 */
        .ql-snow.ql-toolbar .ql-picker.ql-header .ql-picker-item[data-value="1"]::before { content: 'Heading 1' !important; font-size: 18px !important; font-weight: 600 !important; color: #fff !important; }
        .ql-snow.ql-toolbar .ql-picker.ql-header .ql-picker-item[data-value="1"]::after { content: 'Ctrl+Alt+1'; font-size: 10px; color: #9fadbc; }

        .ql-snow.ql-toolbar .ql-picker.ql-header .ql-picker-item[data-value="2"]::before { content: 'Heading 2' !important; font-size: 16px !important; font-weight: 500 !important; color: #e2e8f0 !important; }
        .ql-snow.ql-toolbar .ql-picker.ql-header .ql-picker-item[data-value="2"]::after { content: 'Ctrl+Alt+2'; font-size: 10px; color: #9fadbc; }

        .ql-snow.ql-toolbar .ql-picker.ql-header .ql-picker-item[data-value="3"]::before { content: 'Heading 3' !important; font-size: 15px !important; font-weight: 500 !important; color: #e2e8f0 !important; }
        .ql-snow.ql-toolbar .ql-picker.ql-header .ql-picker-item[data-value="3"]::after { content: 'Ctrl+Alt+3'; font-size: 10px; color: #9fadbc; }

        .ql-snow.ql-toolbar .ql-picker.ql-header .ql-picker-item[data-value="4"]::before { content: 'Heading 4' !important; font-size: 13px !important; font-weight: 600 !important; }
        .ql-snow.ql-toolbar .ql-picker.ql-header .ql-picker-item[data-value="4"]::after { content: 'Ctrl+Alt+4'; font-size: 10px; color: #8c9bab; }

        .ql-snow.ql-toolbar .ql-picker.ql-header .ql-picker-item[data-value="5"]::before { content: 'Heading 5' !important; font-size: 12px !important; font-weight: 600 !important; }
        .ql-snow.ql-toolbar .ql-picker.ql-header .ql-picker-item[data-value="5"]::after { content: 'Ctrl+Alt+5'; font-size: 10px; color: #8c9bab; }

        .ql-snow.ql-toolbar .ql-picker.ql-header .ql-picker-item[data-value="6"]::before { content: 'Heading 6' !important; font-size: 11px !important; font-weight: 600 !important; text-transform: uppercase; color: #8c9bab !important; }
        .ql-snow.ql-toolbar .ql-picker.ql-header .ql-picker-item[data-value="6"]::after { content: 'Ctrl+Alt+6'; font-size: 10px; color: #8c9bab; }

        .ql-snow.ql-toolbar .ql-picker-item:hover {
            background-color: rgba(87, 157, 255, 0.12) !important;
            color: #579dff !important;
        }

        .ql-snow.ql-toolbar .ql-picker-item.ql-selected {
            color: #579dff !important;
            background-color: rgba(87, 157, 255, 0.18) !important;
            border-left: 3px solid #579dff !important;
        }
    </style>
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <script>
        function quillImageHandler() {
            const input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.setAttribute('accept', 'image/*');
            input.click();

            input.onchange = () => {
                const file = input.files[0];
                if (!file) return;

                const formData = new FormData();
                formData.append('file', file);

                fetch(`/boards/${boardId}/lists/${listId}/cards/${cardId}/attachments`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.attachment && data.attachment.file_path) {
                            const cleanPath = data.attachment.file_path.replace(/^\/?(storage\/)?/, '');
                            const url = '/storage/' + cleanPath;
                            const range = this.quill.getSelection(true);
                            this.quill.insertEmbed(range.index, 'image', url);
                            this.quill.setSelection(range.index + 1);
                        }
                    })
                    .catch(error => {
                        showToast('Failed to upload image', 'error');
                    });
            };
        }

        document.addEventListener('DOMContentLoaded', function () {
            const commonToolbar = [
                [{ 'header': [false, 1, 2, 3, 4, 5, 6] }], // Normal Text first, then 1-6!
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                ['link', 'image', 'code-block'],
                ['clean']
            ];

            if (document.getElementById('quillDescriptionEditor')) {
                window.descriptionQuill = new Quill('#quillDescriptionEditor', {
                    theme: 'snow',
                    placeholder: 'Add a more detailed description...',
                    modules: {
                        toolbar: {
                            container: commonToolbar,
                            handlers: { image: quillImageHandler }
                        }
                    }
                });

                window.descriptionQuill.on('selection-change', function (range) {
                    const wrapper = document.querySelector('.description-editor-wrapper');
                    if (range) {
                        wrapper.classList.add('focused');
                    } else {
                        wrapper.classList.remove('focused');
                    }
                });
            }

            if (document.getElementById('quillDescriptionEditor') || document.getElementById('quillCommentEditor')) {
                // Add tooltips to all Quill buttons
                const tooltips = {
                    'ql-header': 'Text Style',
                    'ql-bold': 'Bold',
                    'ql-italic': 'Italic',
                    'ql-underline': 'Underline',
                    'ql-strike': 'Strikethrough',
                    'ql-list[value="ordered"]': 'Ordered List',
                    'ql-list[value="bullet"]': 'Bullet List',
                    'ql-link': 'Insert Link',
                    'ql-image': 'Insert Image',
                    'ql-code-block': 'Code Block',
                    'ql-clean': 'Clear Formatting'
                };

                setTimeout(() => {
                    document.querySelectorAll('.ql-toolbar button, .ql-toolbar .ql-picker').forEach(btn => {
                        for (const selector in tooltips) {
                            if (btn.classList.contains(selector) || btn.querySelector(`.${selector}`)) {
                                btn.setAttribute('data-tooltip', tooltips[selector]);
                            }
                        }
                    });
                }, 500);
            }
            if (document.getElementById('quillCommentEditor')) {
                window.commentQuill = new Quill('#quillCommentEditor', {
                    theme: 'snow',
                    placeholder: 'Write a comment...',
                    modules: {
                        toolbar: {
                            container: commonToolbar,
                            handlers: { image: quillImageHandler }
                        }
                    }
                });

                const commentActions = document.getElementById('commentActions');
                window.commentQuill.on('text-change', function () {
                    const plain = window.commentQuill.getText().trim();
                    const html = window.commentQuill.root.innerHTML.trim();
                    const hasContent = plain || (html && html !== '<p><br></p>');
                    if (commentActions) {
                        commentActions.style.display = hasContent ? 'block' : 'none';
                    }

                    // @mention autocomplete
                    const selection = window.commentQuill.getSelection();
                    if (!selection) return;
                    const textBefore = window.commentQuill.getText(0, selection.index);
                    const atMatch = textBefore.match(/@([\w\s]*)$/);
                    if (atMatch) {
                        const query = atMatch[1].toLowerCase();
                        const matched = allWorkspaceUsers.filter(u => u.name.toLowerCase().includes(query)).slice(0, 6);
                        showMentionSuggestions(matched, selection.index - atMatch[0].length, atMatch[0].length);
                    } else {
                        hideMentionSuggestions();
                    }
                });

                // Focus effect
                window.commentQuill.on('selection-change', function (range) {
                    const wrapper = document.querySelector('.comment-editor-wrapper');
                    if (range) {
                        wrapper.classList.add('focused');
                        document.querySelector('#quillCommentEditor .ql-editor').parentElement.style.minHeight = '100px';
                        document.getElementById('quillCommentEditor').style.background = '#1d2125';
                    } else {
                        const plain = window.commentQuill.getText().trim();
                        const html = window.commentQuill.root.innerHTML.trim();
                        const hasContent = plain || (html && html !== '<p><br></p>');
                        if (!hasContent) {
                            wrapper.classList.remove('focused');
                            document.querySelector('#quillCommentEditor .ql-editor').parentElement.style.minHeight = '40px';
                            document.getElementById('quillCommentEditor').style.background = '#22272b';
                        }
                    }
                });
            }
        });

        // Current user info for comment rendering
        const currentUserId = {{ auth()->id() }};
        const currentUserName = @json(auth()->user()->name);
        const isAdminUser = {{ auth()->user()->isSystemAdmin() ? 'true' : 'false' }};
        const isOwnerUser = {{ $board->workspace->isOwner(auth()->id()) ? 'true' : 'false' }};

        function highlightMentions(html) {
            if (!html) return '';

            // Aggressive Fix for Images:
            // If the image path is relative or missing /storage/, we force it.
            // We also make sure it uses an absolute URL if possible.
            const baseUrl = window.location.origin;
            let processed = html.replace(/src="(?!\/|http)(.*?)"/g, `src="${baseUrl}/storage/$1"`);

            // Fix double storage issues
            processed = processed.replace(/\/storage\/storage\//g, '/storage/');

            return processed.replace(/@([\w][^\s<]{0,40})/g, '<span style="color:#579dff;font-weight:600;">@$1</span>');
        }

        function renderComment(comment) {
            const initials = comment.user.name.substr(0, 2).toUpperCase();
            const isOwn = comment.user_id == currentUserId;
            const editedTag = comment.is_edited && !comment.is_deleted ? '<span style="font-size:10px;color:#9fadbc;margin-left:4px;">(edited)</span>' : '';

            // Deleted comment — only admin sees it
            if (comment.is_deleted) {
                return `<div class="activity-item" data-comment-id="${comment.id}" style="display:flex;gap:12px;margin-bottom:20px;opacity:0.6;">
                    <div style="width:32px;height:32px;border-radius:50%;background:#3d444d;color:#9fadbc;display:flex;align-items:center;justify-content:center;font-weight:600;font-size:12px;flex-shrink:0;">${initials}</div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:13px;color:#9fadbc;margin-bottom:6px;">
                            <strong style="color:#b6c2cf;">${comment.user.name}</strong>
                            <span style="font-size:11px;color:#9fadbc;margin-left:4px;">${comment.created_at_human || ''}</span>
                        </div>
                        <div style="background:#1d2125;padding:10px 12px;border-radius:8px;font-size:13px;color:#9fadbc;font-style:italic;border:1px dashed #3c444d;">
                            🗑️ This message was deleted
                        </div>
                    </div>
                </div>`;
            }

            let actionHtml = '';
            if (isAdminUser) {
                actionHtml = `
                    <span style="cursor:pointer;font-size:11px;color:#9fadbc;text-decoration:underline;margin-left:8px;" onclick="editComment(${comment.id})">Edit</span>
                    <span style="color:#9fadbc;font-size:11px;margin-left:4px;">•</span>
                    <span style="cursor:pointer;font-size:11px;color:#eb5a46;text-decoration:underline;margin-left:4px;" onclick="deleteComment(${comment.id})">Delete</span>`;
            } else if (isOwn) {
                actionHtml = `<span style="cursor:pointer;font-size:11px;color:#9fadbc;text-decoration:underline;margin-left:8px;" onclick="editComment(${comment.id})">Edit</span>`;
            }

            return `<div class="activity-item" data-comment-id="${comment.id}" style="display:flex;gap:12px;margin-bottom:20px;">
                <div style="width:32px;height:32px;border-radius:50%;background:#0079bf;color:white;display:flex;align-items:center;justify-content:center;font-weight:600;font-size:12px;flex-shrink:0;">${initials}</div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:13px;color:#9fadbc;margin-bottom:6px;display:flex;align-items:center;flex-wrap:wrap;gap:4px;">
                        <strong style="color:#b6c2cf;">${comment.user.name}</strong>
                        <span class="activity-timestamp" style="font-size:11px;color:#9fadbc;">${comment.created_at_human || 'just now'}</span>
                        ${editedTag}
                        ${actionHtml}
                    </div>
                    <div class="comment-body" id="comment-body-${comment.id}" style="background:#2c333a;padding:10px 12px;border-radius:8px;font-size:14px;color:#b6c2cf;line-height:1.5;box-shadow:0 1px 2px rgba(0,0,0,0.15);">${highlightMentions(comment.content)}</div>
                </div>
            </div>`;
        }

        function toggleCommentMenu(btn) {
            const menu = btn.nextElementSibling;
            const isOpen = menu.style.display === 'block';
            document.querySelectorAll('.comment-menu').forEach(m => m.style.display = 'none');
            if (!isOpen) {
                const rect = btn.getBoundingClientRect();
                menu.style.position = 'fixed';
                menu.style.top = (rect.bottom + 4) + 'px';
                menu.style.left = rect.left + 'px';
                menu.style.display = 'block';
                setTimeout(() => document.addEventListener('click', function handler(e) {
                    if (!menu.contains(e.target) && e.target !== btn) {
                        menu.style.display = 'none';
                        document.removeEventListener('click', handler);
                    }
                }), 0);
            }
        }

        function editComment(commentId) {
            document.querySelectorAll('.comment-menu').forEach(m => m.style.display = 'none');
            stopPolling();
            const bodyEl = document.getElementById(`comment-body-${commentId}`);
            if (!bodyEl) return;
            const original = bodyEl.innerHTML;
            const plainText = bodyEl.innerText;
            bodyEl.innerHTML = '';

            const textarea = document.createElement('textarea');
            textarea.id = `edit-textarea-${commentId}`;
            textarea.style.cssText = 'width:100%;background:#1d2125;border:1px solid #579dff;border-radius:6px;color:#b6c2cf;font-size:14px;padding:8px;resize:vertical;min-height:60px;box-sizing:border-box;font-family:inherit;';
            textarea.value = plainText;

            const actions = document.createElement('div');
            actions.style.cssText = 'display:flex;gap:8px;margin-top:6px;';

            const saveBtn = document.createElement('button');
            saveBtn.textContent = 'Save';
            saveBtn.style.cssText = 'background:#0c66e4;color:#fff;border:none;border-radius:4px;padding:6px 14px;font-size:13px;font-weight:600;cursor:pointer;';
            saveBtn.onclick = () => saveEditComment(commentId);

            const cancelBtn = document.createElement('button');
            cancelBtn.textContent = 'Cancel';
            cancelBtn.style.cssText = 'background:#3d444d;color:#b6c2cf;border:none;border-radius:4px;padding:6px 14px;font-size:13px;cursor:pointer;';
            cancelBtn.onclick = () => {
                bodyEl.innerHTML = original;
                startPolling();
            };

            actions.appendChild(saveBtn);
            actions.appendChild(cancelBtn);
            bodyEl.appendChild(textarea);
            bodyEl.appendChild(actions);
            textarea.focus();
        }

        function cancelEditComment(commentId, original) {
            const bodyEl = document.getElementById(`comment-body-${commentId}`);
            if (bodyEl) bodyEl.innerHTML = original;
            startPolling();
        }

        function saveEditComment(commentId) {
            const textarea = document.getElementById(`edit-textarea-${commentId}`);
            if (!textarea) return;
            const content = textarea.value.trim();
            if (!content) return;

            fetch(`/boards/${boardId}/lists/${listId}/cards/${cardId}/comments/${commentId}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                body: JSON.stringify({ content })
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        startPolling();
                        fetchActivityAndComments();
                        showToast('Comment updated', 'success');
                    }
                })
                .catch(error => console.error('Error updating comment:', error));
        }

        function addComment() {
            if (!window.commentQuill) return;
            const content = window.commentQuill.root.innerHTML;

            // Allow if has text or image
            const hasText = window.commentQuill.getText().trim() !== '';
            const hasImage = content.includes('<img');
            if (!hasText && !hasImage) return;

            fetch(`/boards/${boardId}/lists/${listId}/cards/${cardId}/comments`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ content: content })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Smooth clear
                        window.commentQuill.setText('');

                        const actions = document.getElementById('commentActions');
                        if (actions) actions.style.display = 'none';

                        const wrapper = document.querySelector('.comment-editor-wrapper');
                        if (wrapper) {
                            wrapper.classList.remove('focused');
                            const qlEditor = wrapper.querySelector('.ql-editor');
                            if (qlEditor) qlEditor.style.minHeight = '40px';
                        }

                        fetchComments();
                    }
                })
                .catch(error => console.error('Error posting comment:', error));
        }

        function renderActivity(a) {
            return `<div class="activity-item" data-activity-id="${a.id}" style="display:flex;gap:12px;margin-bottom:16px;align-items:flex-start;">
                <div style="width:32px;height:32px;border-radius:50%;background:#0052cc;color:white;display:flex;align-items:center;justify-content:center;font-weight:600;font-size:12px;flex-shrink:0;">${a.initials}</div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:13px;color:#9fadbc;margin-bottom:2px;">
                        <strong style="color:#b6c2cf;">${a.user_name}</strong> ${a.message}
                    </div>
                    <div class="activity-timestamp" style="font-size:11px;color:#9fadbc;">${a.diff}</div>
                </div>
            </div>`;
        }

        function fetchActivityAndComments() {
            Promise.all([
                fetch(`/boards/${boardId}/lists/${listId}/cards/${cardId}/comments/poll`, {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                }).then(r => r.json()),
                fetch(`/boards/${boardId}/lists/${listId}/cards/${cardId}/activities`, {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                }).then(r => r.json())
            ]).then(([commentsData, activitiesData]) => {
                const list = document.getElementById('activityList');
                if (!list) return;

                const allComments = (commentsData.comments || []);
                const allActivities = (activitiesData.activities || []);

                // Build unified list with type tag
                let items = allComments.map(c => ({ type: 'comment', ts: new Date(c.created_at).getTime(), data: c }));

                if (showActivities) {
                    allActivities.forEach(a => {
                        items.push({ type: 'activity', ts: new Date(a.created_at || 0).getTime(), data: a });
                    });
                }

                // Sort newest first
                items.sort((a, b) => b.ts - a.ts);

                list.innerHTML = '';
                items.forEach(item => {
                    if (item.type === 'comment') {
                        list.insertAdjacentHTML('beforeend', renderComment(item.data));
                    } else {
                        list.insertAdjacentHTML('beforeend', renderActivity(item.data));
                    }
                });

                // Make images in comments clickable after DOM insert
                list.querySelectorAll('.comment-body img').forEach(img => {
                    if (img.dataset.clickable) return;
                    img.dataset.clickable = '1';
                    img.style.cursor = 'pointer';
                    img.style.maxWidth = '100%';
                    img.style.borderRadius = '4px';
                    img.onclick = function () {
                        const overlay = document.createElement('div');
                        overlay.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.85);z-index:99999;display:flex;align-items:center;justify-content:center;cursor:zoom-out;';
                        const bigImg = document.createElement('img');
                        bigImg.src = img.src;
                        bigImg.style.cssText = 'max-width:90vw;max-height:90vh;border-radius:6px;box-shadow:0 8px 32px rgba(0,0,0,0.5);';
                        overlay.appendChild(bigImg);
                        overlay.onclick = () => overlay.remove();
                        document.body.appendChild(overlay);
                    };
                });

                lastCommentIds = new Set(allComments.map(c => String(c.id)));
            }).catch((err) => { console.error('fetchActivityAndComments error:', err); });
        }

        let lastCommentIds = new Set();
        let pollingInterval = null;

        function startPolling() {
            if (!pollingInterval) {
                pollingInterval = setInterval(fetchActivityAndComments, 5000);
            }
        }

        function stopPolling() {
            if (pollingInterval) {
                clearInterval(pollingInterval);
                pollingInterval = null;
            }
        }

        // Initial load on page ready
        fetchActivityAndComments();
        startPolling();

        // Legacy alias
        function fetchComments() { fetchActivityAndComments(); }

        function deleteComment(commentId) {
            Swal.fire({
                title: 'Delete comment?',
                text: "Are you sure you want to delete this comment?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#eb5a46',
                cancelButtonColor: '#38414a',
                confirmButtonText: 'Delete',
                background: '#22272b',
                color: '#b6c2cf'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/boards/${boardId}/lists/${listId}/cards/${cardId}/comments/${commentId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.querySelector(`[data-comment-id="${commentId}"]`).remove();
                                showToast('Comment deleted');
                            }
                        });
                }
            });
        }

        let showActivities = false; // default: activities hidden, only comments shown

        function toggleDetails() {
            const btn = document.getElementById('toggleDetailsBtn');
            showActivities = !showActivities;
            btn.textContent = showActivities ? 'Hide details' : 'Show details';
            // Re-render with current state
            fetchActivityAndComments();
        }
    </script>
    <script>
        function toggleUserDropdown() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('active');

            // Close when clicking outside
            if (dropdown.classList.contains('active')) {
                const closeHandler = (e) => {
                    const avatar = document.getElementById('userAvatar');
                    if (avatar && !avatar.contains(e.target)) {
                        dropdown.classList.remove('active');
                        document.removeEventListener('click', closeHandler);
                    }
                };
                setTimeout(() => document.addEventListener('click', closeHandler), 0);
            }
        }

        function closeCardView() {
            // If we are in an overlay, we can just hide it
            const overlay = document.querySelector('.card-view-overlay');
            if (overlay) {
                overlay.style.display = 'none';
                // Also redirect back to board since this usually loads via URL
                window.location.href = `/boards/${boardId}`;
            }
        }

        // Update board view card cover in real-time
        function updateBoardCardCover(cardId, coverValue) {
            // Store in localStorage for same-tab updates
            localStorage.setItem('cardUpdate', JSON.stringify({
                cardId: cardId,
                type: 'cover',
                value: coverValue,
                timestamp: Date.now()
            }));

            // Also try to update parent window if opened as popup
            if (window.opener && !window.opener.closed) {
                try {
                    const boardCard = window.opener.document.querySelector(`[data-card-id="${cardId}"]`);
                    if (boardCard) {
                        updateCardCoverInDOM(boardCard, coverValue);
                    }
                } catch (e) {
                    console.log('Cannot access parent window');
                }
            }
        }

        // Update board view card labels in real-time
        function updateBoardCardLabels(cardId, labels) {
            // Store in localStorage for same-tab updates
            localStorage.setItem('cardUpdate', JSON.stringify({
                cardId: cardId,
                type: 'labels',
                value: labels,
                timestamp: Date.now()
            }));

            // Also try to update parent window if opened as popup
            if (window.opener && !window.opener.closed) {
                try {
                    const boardCard = window.opener.document.querySelector(`[data-card-id="${cardId}"]`);
                    if (boardCard) {
                        updateCardLabelsInDOM(boardCard, labels);
                    }
                } catch (e) {
                    console.log('Cannot access parent window');
                }
            }
        }

        // Helper function to update cover in DOM
        function updateCardCoverInDOM(cardElement, coverValue) {
            let coverDiv = cardElement.querySelector('.card-cover');
            const isColor = /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(coverValue);

            if (!coverDiv && coverValue) {
                // Create cover div if it doesn't exist
                coverDiv = document.createElement('div');
                coverDiv.className = 'card-cover';
                cardElement.insertBefore(coverDiv, cardElement.firstChild);
                cardElement.classList.add('has-cover');
            }

            if (coverDiv) {
                if (coverValue) {
                    if (isColor) {
                        coverDiv.style.cssText = `background: ${coverValue};`;
                    } else {
                        coverDiv.style.cssText = `background-image: url('${coverValue}');`;
                    }
                } else {
                    // Remove cover
                    coverDiv.remove();
                    cardElement.classList.remove('has-cover');
                }
            }

            // Update data attribute
            cardElement.setAttribute('data-card-cover', coverValue || '');
        }

        // Helper function to update labels in DOM
        function updateCardLabelsInDOM(cardElement, labels) {
            let labelsDiv = cardElement.querySelector('.card-labels');

            if (!labels || labels.length === 0) {
                if (labelsDiv) labelsDiv.remove();
                return;
            }

            if (!labelsDiv) {
                // Create labels div if it doesn't exist
                labelsDiv = document.createElement('div');
                labelsDiv.className = 'card-labels';
                const cardContent = cardElement.querySelector('.card-content');
                if (cardContent) {
                    cardElement.insertBefore(labelsDiv, cardContent);
                }
            }

            // Clear and rebuild labels
            labelsDiv.innerHTML = '';

            labels.forEach(labelRef => {
                let color = '';

                // If it's an ID, find in boardLabels
                if (typeof labelRef === 'number' || !isNaN(labelRef)) {
                    const fullLabel = boardLabels.find(l => l.id == labelRef);
                    if (fullLabel) {
                        color = fullLabel.color;
                    }
                } else if (typeof labelRef === 'object' && labelRef.color) {
                    color = labelRef.color;
                }

                if (color) {
                    const labelSpan = document.createElement('span');
                    labelSpan.className = 'card-label';
                    labelSpan.style.background = color;
                    labelsDiv.appendChild(labelSpan);
                }
            });

            // Update data attribute
            cardElement.setAttribute('data-card-labels', JSON.stringify(labels));
        }

        // makeAttachmentCover is defined above at the cover section
        // End of Script
    </script>

    {{-- Task 7.1: deleteChecklist() fix + Tasks 7.2-7.5: Echo real-time handlers --}}
    <script>
        // ── Task 7.1: deleteChecklist ──────────────────────────────────────────
        function deleteChecklist() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            fetch(`/boards/${boardId}/lists/${listId}/cards/${cardId}/checklist`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const section = document.getElementById('checklistSection');
                        if (section) section.style.display = 'none';
                        const items = document.getElementById('checklistItems');
                        if (items) items.innerHTML = '';
                    } else {
                        showToast('Failed to delete checklist', 'error');
                    }
                })
                .catch(() => showToast('Failed to delete checklist', 'error'));
        }

        // ── Tasks 7.2-7.5: Echo real-time handlers ────────────────────────────
        const currentCardId = {{ $card->id }};

        // ── Task 7.3: Card-level event handlers ───────────────────────────────
        function handleCardUpdated(data) {
            if (data.card_id !== currentCardId) return;
            const titleInput = document.getElementById('cardTitleInput');
            if (titleInput) titleInput.value = data.title;
            const descText = document.getElementById('descriptionText');
            if (descText) {
                descText.innerHTML = data.description || 'Add a more detailed description...';
                descText.classList.toggle('empty', !data.description || !data.description.trim());
            }
        }

        function handleCardCoverUpdated(data) {
            if (data.card_id !== currentCardId) return;
            if (typeof updateCoverDisplay === 'function') {
                updateCoverDisplay(data.cover);
            } else {
                const coverDisplay = document.querySelector('.card-cover-display');
                if (coverDisplay && data.cover) {
                    const isColor = /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(data.cover);
                    if (isColor) {
                        coverDisplay.style.backgroundImage = '';
                        coverDisplay.style.backgroundColor = data.cover;
                    } else {
                        coverDisplay.style.backgroundColor = '';
                        coverDisplay.style.backgroundImage = `url(${data.cover})`;
                    }
                }
            }
        }

        function handleCardLabelsUpdated(data) {
            if (data.card_id !== currentCardId) return;
            if (typeof updateLabelsDisplay === 'function') {
                updateLabelsDisplay(data.labels);
            }
        }

        function handleCardMoved(data) {
            if (data.card_id !== currentCardId) return;
            showToast('This card was moved to another list', 'info');
        }

        function handleCardArchived(data) {
            if (data.card_id !== currentCardId) return;
            showToast('This card has been archived', 'warning');
            setTimeout(() => {
                window.location.href = '{{ route('boards.show', $board) }}';
            }, 2000);
        }

        // ── Task 7.4: Checklist event handlers ────────────────────────────────
        function recalculateChecklistProgress() {
            const items = document.querySelectorAll('#checklistItems .checklist-item input[type="checkbox"]');
            const total = items.length;
            const completed = Array.from(items).filter(i => i.checked).length;
            const percent = total > 0 ? Math.round((completed / total) * 100) : 0;
            const bar = document.getElementById('checklistProgressBar');
            const pct = document.getElementById('checklistPercent');
            if (bar) bar.style.width = percent + '%';
            if (pct) pct.textContent = percent + '%';
            const section = document.getElementById('checklistSection');
            if (section) section.style.display = total > 0 ? 'block' : 'none';
        }

        function handleChecklistItemCreated(data) {
            if (data.card_id !== currentCardId) return;
            const section = document.getElementById('checklistSection');
            if (section) section.style.display = 'block';
            const container = document.getElementById('checklistItems');
            if (!container) return;
            // Avoid duplicates
            if (container.querySelector(`[data-item-id="${data.id}"]`)) return;
            const itemDiv = document.createElement('div');
            itemDiv.className = 'checklist-item';
            itemDiv.setAttribute('data-item-id', data.id);
            itemDiv.style.cssText = 'display:flex;align-items:flex-start;gap:12px;margin-bottom:8px;';
            itemDiv.innerHTML = `
                <input type="checkbox" onchange="toggleChecklistItem(${data.id}, this.checked)" ${data.is_completed ? 'checked' : ''} style="margin-top:3px;">
                <label style="flex:1;font-size:14px;color:#b6c2cf;${data.is_completed ? 'text-decoration:line-through;opacity:0.6;' : ''}">${data.title}</label>
                <button onclick="deleteChecklistItem(${data.id})" style="background:none;border:none;color:#9fadbc;cursor:pointer;">×</button>
            `;
            container.appendChild(itemDiv);
            recalculateChecklistProgress();
        }

        function handleChecklistItemUpdated(data) {
            if (data.card_id !== currentCardId) return;
            const itemEl = document.querySelector(`#checklistItems [data-item-id="${data.id}"]`);
            if (!itemEl) return;
            const checkbox = itemEl.querySelector('input[type="checkbox"]');
            const label = itemEl.querySelector('label');
            if (checkbox) checkbox.checked = data.is_completed;
            if (label) {
                label.textContent = data.title;
                label.style.textDecoration = data.is_completed ? 'line-through' : '';
                label.style.opacity = data.is_completed ? '0.6' : '';
            }
            recalculateChecklistProgress();
        }

        function handleChecklistItemDeleted(data) {
            if (data.card_id !== currentCardId) return;
            const itemEl = document.querySelector(`#checklistItems [data-item-id="${data.id}"]`);
            if (itemEl) itemEl.remove();
            recalculateChecklistProgress();
        }

        function handleChecklistCleared(data) {
            if (data.card_id !== currentCardId) return;
            const section = document.getElementById('checklistSection');
            if (section) section.style.display = 'none';
            const container = document.getElementById('checklistItems');
            if (container) container.innerHTML = '';
        }

        // -- Task 7.5: Member and comment event handlers
        function handleCardMemberAdded(data) {
            if (data.card_id !== currentCardId) return;
            const userId = data.user.id;
            cardMemberIds.add(userId);
            addInlineMemberAvatar(userId, data.user.name);
            const list = document.getElementById('currentMembersList');
            if (list && !list.querySelector('[data-user-id="' + userId + '"]')) {
                const initials = data.user.name.substring(0, 2).toUpperCase();
                const div = document.createElement('div');
                div.className = 'ms-item';
                div.setAttribute('data-user-id', userId);
                div.style.cssText = 'display:flex;align-items:center;gap:10px;padding:6px 8px;border-radius:4px;cursor:pointer;';
                div.onclick = () => toggleCardMember(userId, data.user.name, true, div);
                div.innerHTML = '<div style="width:32px;height:32px;border-radius:50%;background:#0052cc;color:#fff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:600;">' + initials + '</div><span style="flex:1;font-size:14px;color:#b6c2cf;">' + data.user.name + '</span><span style="color:#579dff;font-size:16px;font-weight:bold;">&#10003;</span>';
                list.appendChild(div);
            }
        }

        function handleCardMemberRemoved(data) {
            if (data.card_id !== currentCardId) return;
            const userId = data.user_id;
            cardMemberIds.delete(userId);
            removeInlineMemberAvatar(userId);
            const el = document.querySelector('#currentMembersList [data-user-id="' + userId + '"]');
            if (el) el.remove();
        }

        function handleCommentPosted(data) {
            if (data.card_id !== currentCardId) return;
            const list = document.getElementById('activityList');
            if (!list) return;
            // Deduplication: skip if own comment already in DOM
            if (data.user && data.user.id === currentUserId) {
                if (document.querySelector(`[data-comment-id="${data.id}"]`)) return;
            }
            const commentHtml = renderComment({
                id: data.id,
                user: data.user,
                user_id: data.user ? data.user.id : null,
                content: data.content,
                created_at_human: data.created_at_human
            });
            list.insertAdjacentHTML('afterbegin', commentHtml);
        }

        function handleCommentDeleted(data) {
            if (data.card_id !== currentCardId) return;
            const el = document.querySelector(`[data-comment-id="${data.comment_id}"]`);
            if (el) el.remove();
        }

        // ── Task 7.2: Echo subscription ───────────────────────────────────────
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof window.Echo !== 'undefined') {
                window.Echo.private('board.{{ $board->id }}')
                    .listen('.CardUpdated', handleCardUpdated)
                    .listen('.ChecklistItemCreated', handleChecklistItemCreated)
                    .listen('.ChecklistItemUpdated', handleChecklistItemUpdated)
                    .listen('.ChecklistItemDeleted', handleChecklistItemDeleted)
                    .listen('.ChecklistCleared', handleChecklistCleared)
                    .listen('.CardMemberAdded', handleCardMemberAdded)
                    .listen('.CardMemberRemoved', handleCardMemberRemoved)
                    .listen('.CardCoverUpdated', handleCardCoverUpdated)
                    .listen('.CommentPosted', handleCommentPosted)
                    .listen('.CommentDeleted', handleCommentDeleted)
                    .listen('.CardLabelsUpdated', handleCardLabelsUpdated)
                    .listen('.CardMoved', handleCardMoved)
                    .listen('.CardArchived', handleCardArchived);
            }

            // ── Polling fallback (works without Pusher) ────────────────────
            let lastUpdatedAt = '{{ $card->updated_at?->toISOString() }}';
            let lastCover = @json($card->cover ?? '');
            let pollActive = true;

            async function pollCard() {
                if (!pollActive) return;
                try {
                    const res = await fetch(`/boards/${boardId}/lists/${listId}/cards/${cardId}/poll`, {
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                    });
                    if (!res.ok) return;
                    const data = await res.json();

                    // Cover - check independently (fast feedback)
                    if (data.cover !== lastCover) {
                        lastCover = data.cover;
                        updateCoverDisplay(data.cover || '');
                    }

                    // Attachments — always sync regardless of updated_at
                    if (data.attachments) syncAttachments(data.attachments);

                    // Only process rest if something changed
                    if (data.updated_at === lastUpdatedAt) return;
                    lastUpdatedAt = data.updated_at;

                    // Title
                    const titleInput = document.getElementById('cardTitleInput');
                    if (titleInput && document.activeElement !== titleInput) titleInput.value = data.title;

                    // Description
                    const descText = document.getElementById('descriptionText');
                    if (descText && document.getElementById('descriptionEditor').style.display === 'none') {
                        descText.innerHTML = data.description || 'Add a more detailed description...';
                        descText.classList.toggle('empty', !data.description || !data.description.trim());
                    }

                    // Cover - already handled above independently

                    // Labels
                    if (typeof updateLabelsDisplay === 'function') updateLabelsDisplay(data.labels);

                    // Archived
                    if (data.is_archived) {
                        showToast('This card has been archived', 'warning');
                        pollActive = false;
                        setTimeout(() => { window.location.href = '/boards/' + boardId; }, 2000);
                        return;
                    }

                    // Checklist sync
                    syncChecklist(data.checklist_items);

                    // Comments sync
                    syncComments(data.comments);

                } catch (e) { /* silent */ }
            }

            function syncAttachments(attachments) {
                const container = document.getElementById('attachmentsList');
                if (!container) return;

                const section = container.closest('.attachments-section');
                if (!attachments || attachments.length === 0) {
                    if (section) section.style.display = 'none';
                    container.innerHTML = '';
                    return;
                }
                if (section) section.style.display = 'block';

                const serverIds = new Set(attachments.map(a => a.id));

                // Remove deleted
                container.querySelectorAll('[data-attachment-id]').forEach(el => {
                    if (!serverIds.has(parseInt(el.getAttribute('data-attachment-id')))) el.remove();
                });

                // Add new
                const existingIds = new Set(
                    Array.from(container.querySelectorAll('[data-attachment-id]')).map(el => parseInt(el.getAttribute('data-attachment-id')))
                );

                attachments.forEach(a => {
                    if (existingIds.has(a.id)) return;
                    const ext = (a.name || '').split('.').pop().toUpperCase();
                    const thumb = a.is_image
                        ? `<div onclick="previewImage('${a.file_url}','${a.name}')" style="width:112px;height:80px;background:#3d444d;border-radius:3px;background-image:url('${a.file_url}');background-size:cover;background-position:center;flex-shrink:0;cursor:pointer;"></div>`
                        : `<div style="width:112px;height:80px;background:#3d444d;border-radius:3px;display:flex;align-items:center;justify-content:center;font-size:18px;color:#9fadbc;font-weight:bold;flex-shrink:0;">${ext || 'FILE'}</div>`;
                    const div = document.createElement('div');
                    div.className = 'attachment-item';
                    div.setAttribute('data-attachment-id', a.id);
                    div.style.cssText = 'display:flex;gap:16px;margin-bottom:12px;padding:8px;border-radius:6px;cursor:pointer;';
                    div.onmouseover = () => div.style.background = '#2c333a';
                    div.onmouseout = () => div.style.background = 'transparent';
                    div.innerHTML = `${thumb}
                        <div style="flex:1;min-width:0;display:flex;flex-direction:column;justify-content:center;">
                            <div style="font-size:14px;font-weight:700;color:#b6c2cf;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${a.name}</div>
                            <div style="font-size:12px;color:#9fadbc;margin:4px 0 8px;">${a.created_at}</div>
                            <div style="display:flex;gap:12px;font-size:13px;">
                                ${!a.is_image ? `<a href="${a.file_url}" download="${a.name}" style="color:#9fadbc;text-decoration:underline;">Download</a>` : ''}
                                <span style="color:#9fadbc;text-decoration:underline;cursor:pointer;" onclick="deleteAttachment(${a.id})">Delete</span>
                                ${a.is_image ? `<span style="color:#9fadbc;text-decoration:underline;cursor:pointer;" onclick="makeAttachmentCover('${a.file_url}')">Make cover</span>` : ''}
                            </div>
                        </div>`;
                    container.prepend(div);
                });
            }

            function syncChecklist(items) {
                const container = document.getElementById('checklistItems');
                const section = document.getElementById('checklistSection');
                if (!container) return;

                if (!items || items.length === 0) {
                    container.innerHTML = '';
                    if (section) section.style.display = 'none';
                    return;
                }

                if (section) section.style.display = 'block';

                // Add/update items
                const existingIds = new Set();
                items.forEach(item => {
                    existingIds.add(item.id);
                    let el = container.querySelector(`[data-item-id="${item.id}"]`);
                    if (!el) {
                        el = document.createElement('div');
                        el.className = 'checklist-item';
                        el.setAttribute('data-item-id', item.id);
                        el.style.cssText = 'display:flex;align-items:flex-start;gap:12px;margin-bottom:8px;';
                        container.appendChild(el);
                    }
                    const cb = el.querySelector('input[type="checkbox"]');
                    // Don't overwrite if user is actively interacting
                    if (!cb || document.activeElement !== cb) {
                        el.innerHTML = `
                            <input type="checkbox" onchange="toggleChecklistItem(${item.id}, this.checked)" ${item.is_completed ? 'checked' : ''} style="margin-top:3px;">
                            <label style="flex:1;font-size:14px;color:#b6c2cf;${item.is_completed ? 'text-decoration:line-through;opacity:0.6;' : ''}">${item.title}</label>
                            <button onclick="deleteChecklistItem(${item.id})" style="background:none;border:none;color:#9fadbc;cursor:pointer;">×</button>
                        `;
                    }
                });

                // Remove deleted items
                container.querySelectorAll('[data-item-id]').forEach(el => {
                    if (!existingIds.has(parseInt(el.getAttribute('data-item-id')))) el.remove();
                });

                recalculateChecklistProgress();
            }

            function syncComments(comments) {
                const list = document.getElementById('activityList');
                if (!list || !comments) return;

                const existingIds = new Set(
                    Array.from(list.querySelectorAll('[data-comment-id]')).map(el => parseInt(el.getAttribute('data-comment-id')))
                );

                comments.forEach(c => {
                    if (!existingIds.has(c.id)) {
                        const html = renderComment(c);
                        list.insertAdjacentHTML('afterbegin', html);
                    }
                });

                // Remove deleted comments
                const serverIds = new Set(comments.map(c => c.id));
                list.querySelectorAll('[data-comment-id]').forEach(el => {
                    if (!serverIds.has(parseInt(el.getAttribute('data-comment-id')))) el.remove();
                });
            }

            // Poll every 3 seconds
            setInterval(pollCard, 3000);

            // Stop polling when tab is hidden, resume when visible
            document.addEventListener('visibilitychange', () => {
                pollActive = !document.hidden;
                if (!document.hidden) {
                    // Tab visible — immediately refresh
                    fetchActivityAndComments();
                    loadNotifications();
                }
            });
        });
        function updateCardClient(clientId) {
            const cardId = {{ $card->id }};
            const listId = {{ $list->id }};
            const boardId = {{ $board->id }};
            
            fetch(`${window.location.origin}/boards/${boardId}/lists/${listId}/cards/${cardId}/client`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ client_id: clientId || null })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Card client updated',
                        showConfirmButton: false,
                        timer: 2000,
                        background: '#22272b',
                        color: '#b6c2cf'
                    });
                }
            });
        }
    </script>
</body>

</html>