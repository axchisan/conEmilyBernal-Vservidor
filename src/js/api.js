export async function registerUser(user) {
  const response = await fetch('../php/login_supabase.php', {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      email: user.email,
      name: user.user_metadata.full_name,
    }),
  });

  if (!response.ok) {
    await response.text();
    return;
  }

  const data = await response.json();

  if (data.redirect) {
    
    await new Promise(resolve => setTimeout(resolve, 500)); 
    window.location.href = data.redirect; 
  }
}