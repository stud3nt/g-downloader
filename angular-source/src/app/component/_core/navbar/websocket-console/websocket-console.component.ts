import { Component, OnInit } from '@angular/core';
import { WebSocketService } from "../../../../service/web-socket.service";
import { RouterService } from "../../../../service/router.service";
import { ConfigService } from "../../../../service/config.service";
import {DomSanitizer, SafeHtml} from "@angular/platform-browser";

@Component({
    selector: 'app-websocket-console',
    templateUrl: './websocket-console.component.html',
    styleUrls: ['./websocket-console.component.scss']
})
export class WebsocketConsoleComponent implements OnInit {

    public websocketClass: string = "dropdown-menu console-dropdown-menu";
    public iframeHTML: SafeHtml = "";

    private consoleVisible: boolean = false;
    private consoleInitialized: boolean = false;

    constructor(
        private websocketService: WebSocketService,
        private routerService: RouterService,
        private configService: ConfigService,
        private sanitizer: DomSanitizer
    ) { }

    ngOnInit() {
        this.initializeConsole();
    }

    /**
     * Console initialization/restart
     */
    public initializeConsole(): void {
        this.createIframe();
        this.consoleInitialized = true;
    }

    /**
     * Show/hide console window
     */
    public toggleConsole(): void {
        this.consoleVisible = !this.consoleVisible;
        this.websocketClass = "dropdown-menu console-dropdown-menu" + (this.consoleVisible ? " visible" : " hidden");
    }

    /**
     * Creates console iframe
     */
    public createIframe(): void {
        let iframeURL: string = this.routerService.generateUrl('app_websocket_server', null, this.configService.websocketUrl);

        this.iframeHTML = null;
        this.iframeHTML = this.sanitizer.bypassSecurityTrustHtml("<iframe id=\"inlineFrameExample\"\n" +
            "    title=\"Console iframe\"\n" +
            "    src=\"" + iframeURL + "\">\n" +
            "</iframe>");
    }

}
