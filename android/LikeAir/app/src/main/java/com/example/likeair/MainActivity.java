package com.example.likeair;

import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.app.AppCompatActivity;

import android.content.Intent;
import android.os.Bundle;
import android.widget.Button;
import android.widget.EditText;
import android.widget.TextView;
import android.view.View;

public class MainActivity extends AppCompatActivity {

    private Button Button_login;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        Button btn_login  = (Button) findViewById(R.id.Button_login);
    }

    public void click_login(View view){
        Custom_dialog_welcome welcome = new Custom_dialog_welcome(this);
        welcome.show();
    }


}
