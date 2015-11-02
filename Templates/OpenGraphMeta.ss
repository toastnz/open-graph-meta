<meta property="og:title" content="<% if $OGTitle %>$OGTitle<% else %>$Title<% end_if %>"/>
<meta property="og:url" content="<% if $OGUrl %>$OGUrl<% else %>$AbsoluteLink<% end_if %>"/>
<meta property="og:type" content="<% if $OGContent %>$OGContent<% else %>website<% end_if %>"/>
<meta property="og:description" content="<% if $OGDescription %>$OGDescription<% else %>$Content.FirstParagraph<% end_if %>">
<meta property="og:image" content="<% if $OGImage %>$OGImage.AbsoluteURL<% else_if $FirstImage %>$FirstImage <% else_if $BannerImage %>$BannerImage.URL<% end_if %>"/>