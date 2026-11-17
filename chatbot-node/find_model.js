const NVIDIA_API_KEY = "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";

async function findWorkingModel() {
    console.log("Fetching models...");
    try {
        const res = await fetch("https://integrate.api.nvidia.com/v1/models", {
            headers: { "Authorization": `Bearer ${NVIDIA_API_KEY}` }
        });
        const data = await res.json();
        const models = data.data.map(m => m.id);
        console.log(`Found ${models.length} models. Testing for access...`);

        // Test models one by one
        for (const model of models) {
            // Skip non-chat/embedding models quickly by looking at name
            if (model.includes("embed") || model.includes("reward") || model.includes("nemoguard")) continue;
            
            console.log(`Testing model: ${model}...`);
            try {
                const chatRes = await fetch("https://integrate.api.nvidia.com/v1/chat/completions", {
                    method: "POST",
                    headers: {
                        "Authorization": `Bearer ${NVIDIA_API_KEY}`,
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        model: model,
                        messages: [{ role: "user", content: "hi" }],
                        max_tokens: 5
                    })
                });
                
                if (chatRes.ok) {
                    console.log(`\n✅ SUCCESS! The working model is: ${model}`);
                    return;
                } else {
                    const err = await chatRes.json();
                    console.log(`  - Failed: ${err.detail || err.title || chatRes.status}`);
                }
            } catch (e) {
                console.log(`  - Error testing ${model}`);
            }
        }
        console.log("\n❌ No models worked for this API key.");
    } catch(err) {
        console.error("Fatal error:", err);
    }
}

findWorkingModel();
