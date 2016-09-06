<?= link_to(
  $notification->email,
  '@user_collection?action=filter&user_filters[email][text]=' . $notification->email
) ?>