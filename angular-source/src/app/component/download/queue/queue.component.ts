import { Component, OnInit } from '@angular/core';
import { ContentHeaderDataService } from "../../../service/data/content-header-data.service";
import { DownloaderService } from "../../../service/downloader.service";
import { JsonResponse } from "../../../model/json-response";
import { ParserType } from "../../../enum/parser-type";
import { DownloadingStatus } from "../../../enum/downloading-status";
import { QueueRequest } from "../../../model/request/queue-request";
import {QueueService} from "../../../service/queue.service";

@Component({
  selector: 'app-queue',
  templateUrl: './queue.component.html',
  styleUrls: ['./queue.component.scss']
})
export class QueueComponent implements OnInit {

    public queueRequest: QueueRequest = new QueueRequest();

    public parserTypeData: object = ParserType.getData();

    public downloadingPackageSize: number = 6;

    public loadingList: boolean = false;

    private checkFilesStatusTimeout = null;

    constructor(
        private headerData: ContentHeaderDataService,
        private downloaderService: DownloaderService,
        private queueService: QueueService
    ) {}

    ngOnInit() {
        this.headerData.setElement('title1', 'Download queue');
        this.headerData.setElement('title2', 'Detailed list of queued and operated files');
        this.getQueueFiles();
    }

    /**
     * Loading files list
     */
    public getQueueFiles(): void {
        this.queueRequest.status = DownloadingStatus.WaitingForResponse
        this.loadingList = true;

        this.queueService.getQueuedFilesPackage(this.queueRequest).subscribe((response: JsonResponse) => {
            this.queueRequest.status = DownloadingStatus.Idle;
            this.loadingList = false;
        }, (error) => {
            this.queueRequest.status = DownloadingStatus.Idle;
            this.loadingList = false;
        });
    }

    /**
     * Executes downloading file package action based on queue settings;
     */
    public downloadFilePackage(): void {
        this.downloaderService.downloadProcess(this.downloadingPackageSize).subscribe((response: JsonResponse) => {
            if (response.data.downloadingStatus === DownloadingStatus.Downloading) {

                this.downloadFilePackage();
            } else {
                this.endDownload();
            }
        });
    }

    /**
     * Ends downloading process;
     */
    public endDownload(): void {
        this.queueRequest.status = DownloadingStatus.Breaking;

        this.downloaderService.stopDownload().subscribe(() => {
            this.queueRequest.status = DownloadingStatus.Idle;
        });
    }

    /**
     * Read files status from cache;
     */
    public checkFilesStatus(): void {

    }
}
