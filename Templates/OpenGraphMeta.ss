<meta property="og:title" content="<% if $OGTitle %>OGTitle => $OGTitle<% else %>Title => $Title<% end_if %>"/>
<meta property="og:url" content="<% if $OGUrl %>OGUrl => $OGUrl<% else %>URL => $AbsoluteLink<% end_if %>"/>
<meta property="og:type" content="<% if $OGContent %>OGContent => $OGContent<% else %>Content => website<% end_if %>"/>
<meta property="og:description" content="<% if $OGDescription %>OGDescription => $OGDescription<% else %>Description => $Content.FirstParagraph<% end_if %>">
<meta property="og:image" content="<% if $OGImage %>OGImage => $OGImage.AbsoluteURL<% else_if $FirstImage %>Image => $FirstImage <% else_if $BannerImage %>$BannerImage.URL<% end_if %>"/>