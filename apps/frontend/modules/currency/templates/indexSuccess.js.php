exchange_rates = <?php echo json_encode($rates) ?>;
function exchange (amount, from, to) {
  return amount * exchange_rates[from][to];
}