"use client";

import { useState, useRef, useEffect } from "react";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Textarea } from "@/components/ui/textarea";
import { Label } from "@/components/ui/label";

interface ReadingCategory {
  id: number;
  name: string;
}

export function ReadingPractice() {
  const [topic, setTopic] = useState("");
  const [level, setLevel] = useState("beginner");
  const [language, setLanguage] = useState("en-US");
  const [readingCategoryId, setReadingCategoryId] = useState<number | null>(null);
  const [categories, setCategories] = useState<ReadingCategory[]>([]);
  const [generatedText, setGeneratedText] = useState("");
  const [feedback, setFeedback] = useState(null);
  const [isRecording, setIsRecording] = useState(false);
  const [audioBlob, setAudioBlob] = useState<Blob | null>(null);
  const mediaRecorder = useRef<MediaRecorder | null>(null);

  useEffect(() => {
    const fetchCategories = async () => {
      const response = await fetch("http://localhost:8000/api/reading-practice/categories");
      const data = await response.json();
      setCategories(data);
      if (data.length > 0) {
        setReadingCategoryId(data[0].id);
      }
    };
    fetchCategories();
  }, []);

  const generateText = async () => {
    const response = await fetch("http://localhost:8000/api/reading-practice/generate-text", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "Accept": "application/json",
      },
      body: JSON.stringify({ topic, level }),
    });
    const data = await response.json();
    setGeneratedText(data.text);
  };

  const startRecording = async () => {
    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
    mediaRecorder.current = new MediaRecorder(stream);
    mediaRecorder.current.start();

    const audioChunks: Blob[] = [];
    mediaRecorder.current.addEventListener("dataavailable", (event) => {
      audioChunks.push(event.data);
    });

    mediaRecorder.current.addEventListener("stop", () => {
      const audioBlob = new Blob(audioChunks, { type: "audio/mp3" });
      setAudioBlob(audioBlob);
    });

    setIsRecording(true);
  };

  const stopRecording = () => {
    if (mediaRecorder.current) {
      mediaRecorder.current.stop();
      setIsRecording(false);
    }
  };

  const analyzeRecording = async () => {
    if (!audioBlob) return;

    const formData = new FormData();
    formData.append("audio", audioBlob, "recording.mp3");
    formData.append("text", generatedText);

    const response = await fetch("http://localhost:8000/api/reading-practice/analyze-recording", {
      method: "POST",
      headers: {
        "Accept": "application/json",
      },
      body: formData,
    });
    const data = await response.json();
    setFeedback(data);
  };

  const saveSession = async () => {
    if (!audioBlob || !feedback || !readingCategoryId) return;

    const formData = new FormData();
    formData.append("audio", audioBlob, "recording.mp3");
    formData.append("text", generatedText);
    formData.append("feedback", JSON.stringify(feedback));
    formData.append("user_id", "1"); // Hardcoded user_id
    formData.append("reading_category_id", readingCategoryId.toString());
    formData.append("language", language);


    const response = await fetch("http://localhost:8000/api/reading-practice/save", {
      method: "POST",
      headers: {
        "Accept": "application/json",
      },
      body: formData,
    });

    if (response.ok) {
      alert("Session saved successfully!");
    } else {
      alert("Failed to save session.");
    }
  };

  return (
    <div className="space-y-6">
      <Card>
        <CardHeader>
          <CardTitle>Reading Practice</CardTitle>
          <CardDescription>
            Generate a text to practice your reading and pronunciation.
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="space-y-2">
            <Label htmlFor="topic">Topic</Label>
            <Textarea
              id="topic"
              placeholder="Enter a topic"
              value={topic}
              onChange={(e) => setTopic(e.target.value)}
            />
          </div>
          <div className="space-y-2">
            <Label htmlFor="level">Level</Label>
            <Select value={level} onValueChange={setLevel}>
              <SelectTrigger>
                <SelectValue placeholder="Select a level" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="beginner">Beginner</SelectItem>
                <SelectItem value="intermediate">Intermediate</SelectItem>
                <SelectItem value="advanced">Advanced</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div className="space-y-2">
            <Label htmlFor="language">Language</Label>
            <Select value={language} onValueChange={setLanguage}>
              <SelectTrigger>
                <SelectValue placeholder="Select a language" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="en-US">English (US)</SelectItem>
                <SelectItem value="en-GB">English (UK)</SelectItem>
                <SelectItem value="es-ES">Spanish</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div className="space-y-2">
            <Label htmlFor="category">Category</Label>
            <Select
              value={readingCategoryId?.toString()}
              onValueChange={(value) => setReadingCategoryId(Number(value))}
            >
              <SelectTrigger>
                <SelectValue placeholder="Select a category" />
              </SelectTrigger>
              <SelectContent>
                {categories.map((category) => (
                  <SelectItem key={category.id} value={category.id.toString()}>
                    {category.name}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>
        </CardContent>
        <CardFooter>
          <Button onClick={generateText}>Generate Text</Button>
        </CardFooter>
      </Card>

      {generatedText && (
        <Card>
          <CardHeader>
            <CardTitle>Generated Text</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <p>{generatedText}</p>
            <div className="flex gap-4">
              <Button onClick={startRecording} disabled={isRecording}>
                Start Recording
              </Button>
              <Button onClick={stopRecording} disabled={!isRecording}>
                Stop Recording
              </Button>
            </div>
            {audioBlob && (
              <div className="space-y-2">
                <audio src={URL.createObjectURL(audioBlob)} controls />
                <Button onClick={analyzeRecording}>Analyze Recording</Button>
              </div>
            )}
          </CardContent>
        </Card>
      )}

      {feedback && (
        <Card>
          <CardHeader>
            <CardTitle>Feedback</CardTitle>
          </CardHeader>
          <CardContent>
            <pre>{JSON.stringify(feedback, null, 2)}</pre>
            <Button onClick={saveSession}>Save Session</Button>
          </CardContent>
        </Card>
      )}
    </div>
  );
}
